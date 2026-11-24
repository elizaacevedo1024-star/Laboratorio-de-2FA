<?php
session_start();
require __DIR__ . '/clases/mysql.inc.php';
require __DIR__ . '/clases/Csrf.php';
require __DIR__ . '/clases/HashBcrypt.php';
require __DIR__ . '/clases/RegistroUsuario.php';

$errores = [];
$v = ['nombre'=>'','apellido'=>'','usuario'=>'','correo'=>'','sexo'=>'M'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validar($_POST['csrf_token'] ?? null)) {
        $errores[] = 'Token de seguridad inválido. Recarga la página e intenta de nuevo.';
    } else {
        $reg = new RegistroUsuario($_POST, new mod_db(), new HashBcrypt());
        if ($reg->registrar()) {
            // Al crear el registro se solicita el QR (2FA) antes de poder entrar
            session_regenerate_id(true);
            Csrf::renovar();
            $_SESSION['qr_usuario'] = $reg->getUsuario();
            $_SESSION['qr_origen']  = 'registro';
            header('Location: activar_2fa.php'); exit;
        }
        $errores = $reg->getErrores() ?: ['No se pudo registrar. Intenta de nuevo.'];
        foreach ($v as $k => $_) $v[$k] = $reg->getDato($k);
    }
}
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registro</title><link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-body">
<main class="card" style="max-width:520px">
  <div class="brand"><img src="patria/5564844.png" alt=""> UTP &middot; Desarrollo Web</div>
  <h1>Crear cuenta</h1>
  <p class="sub">Al terminar te mostraremos un código QR para activar la verificación en dos pasos.</p>

  <?php if ($errores): ?><div class="alert err"><ul><?php foreach ($errores as $e) echo '<li>'.$h($e).'</li>'; ?></ul></div><?php endif; ?>

  <form id="frmRegistro" method="post" novalidate>
    <?= Csrf::campo() ?>
    <div class="row">
      <div><label for="nombre">Nombre</label><input id="nombre" name="nombre" value="<?= $h($v['nombre']) ?>" required><div class="hint"></div></div>
      <div><label for="apellido">Apellido</label><input id="apellido" name="apellido" value="<?= $h($v['apellido']) ?>" required><div class="hint"></div></div>
    </div>
    <label for="usuario">Usuario</label>
    <input id="usuario" name="usuario" value="<?= $h($v['usuario']) ?>" minlength="4" maxlength="30" required autocomplete="username">
    <div class="hint" id="h-usuario"></div>

    <label for="correo">Correo electrónico</label>
    <input id="correo" name="correo" type="email" value="<?= $h($v['correo']) ?>" required autocomplete="email">
    <div class="hint" id="h-correo"></div>

    <div class="row">
      <div><label for="clave">Contraseña</label><input id="clave" name="clave" type="password" minlength="8" required autocomplete="new-password">
        <div class="meter"><span id="meter"></span></div><div class="hint" id="h-clave">Mínimo 8 caracteres</div></div>
      <div><label for="clave2">Confirmar contraseña</label><input id="clave2" name="clave2" type="password" required autocomplete="new-password"><div class="hint" id="h-clave2"></div></div>
    </div>
    <label for="sexo">Sexo</label>
    <select id="sexo" name="sexo"><option value="M" <?= $v['sexo']==='M'?'selected':'' ?>>M</option><option value="F" <?= $v['sexo']==='F'?'selected':'' ?>>F</option></select>

    <button type="submit" id="btn">Registrarme</button>
  </form>
  <p class="links">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
</main>

<script>
(function(){
  var f=document.getElementById('frmRegistro'), dup={usuario:false,correo:false};
  function hint(id,msg,cls){var e=document.getElementById(id); if(e){e.textContent=msg; e.className='hint '+(cls||'');}}
  function mark(el,ok){el.classList.toggle('valid',ok); el.classList.toggle('invalid',!ok);}

  function checkDup(campo, el){
    var val=el.value.trim(); if(!val) return Promise.resolve();
    return fetch('verificar_usuario.php?campo='+campo+'&valor='+encodeURIComponent(val))
      .then(r=>r.json()).then(function(j){
        dup[campo]=j.existe;
        hint('h-'+campo, j.existe ? 'Ya está registrado, elige otro.' : 'Disponible', j.existe?'err':'ok');
        mark(el,!j.existe);
      }).catch(function(){});
  }
  function vUsuario(){var el=f.usuario; if(!/^[A-Za-z0-9._\-]{4,30}$/.test(el.value.trim())){hint('h-usuario','Mín. 4 caracteres: letras, números, . _ -','err');mark(el,false);return false;} return true;}
  function vCorreo(){var el=f.correo; if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value.trim())){hint('h-correo','Correo no válido','err');mark(el,false);return false;} return true;}
  function vClave(){var el=f.clave,p=el.value,s=0; if(p.length>=8)s++; if(/[A-Z]/.test(p))s++; if(/[0-9]/.test(p))s++; if(/[^A-Za-z0-9]/.test(p))s++;
    var m=document.getElementById('meter'); m.style.width=(s*25)+'%'; m.style.background=['#c0392b','#c0392b','#e6a817','#7cb342','#1a8f4c'][s];
    if(p.length<8){hint('h-clave','Mínimo 8 caracteres','err');mark(el,false);return false;} hint('h-clave','','');mark(el,true);return true;}
  function vClave2(){var el=f.clave2; if(el.value!==f.clave.value||!el.value){hint('h-clave2','Las contraseñas no coinciden','err');mark(el,false);return false;} hint('h-clave2','Coinciden','ok');mark(el,true);return true;}
  function vReq(el){var ok=el.value.trim()!==''; mark(el,ok); return ok;}

  f.usuario.addEventListener('blur',function(){ if(vUsuario()) checkDup('usuario',f.usuario); });
  f.correo.addEventListener('blur', function(){ if(vCorreo())  checkDup('correo',f.correo); });
  f.clave.addEventListener('input',function(){vClave(); if(f.clave2.value) vClave2();});
  f.clave2.addEventListener('input',vClave2);

  f.addEventListener('submit',function(e){
    var ok=[vReq(f.nombre),vReq(f.apellido),vUsuario(),vCorreo(),vClave(),vClave2()].every(Boolean);
    if(!ok||dup.usuario||dup.correo){e.preventDefault(); if(dup.usuario||dup.correo) alert('Corrige los datos duplicados.');}
  });
})();
</script>
</body></html>
