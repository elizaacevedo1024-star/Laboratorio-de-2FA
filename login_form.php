<?php
require_once __DIR__ . '/clases/Csrf.php';
$emsg = $_SESSION['emsg'] ?? 0;  unset($_SESSION['emsg']);
$okmsg = $_SESSION['ok_msg'] ?? ''; unset($_SESSION['ok_msg']);
?>
<!DOCTYPE html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="Description" content="Ejemplo de Login con 2FA">
<link rel="shortcut icon" href="patria/5564844.png">
<link rel="stylesheet" href="css/auth.css">
<title>Iniciar sesión</title>
</head>
<body class="auth-body">
<main class="card">
  <div class="brand"><img src="patria/5564844.png" alt=""> UTP &middot; Desarrollo Web</div>
  <h1>Iniciar sesión</h1>
  <p class="sub">Ingresa tus credenciales. Luego te pediremos el código de Google Authenticator.</p>

  <?php if ($emsg == 1): ?><div class="alert err">Usuario o contraseña incorrectos.</div><?php endif; ?>
  <?php if ($emsg == 2): ?><div class="alert err">La sesión del formulario expiró. Intenta de nuevo.</div><?php endif; ?>
  <?php if ($okmsg): ?><div class="alert ok"><?= htmlspecialchars($okmsg) ?></div><?php endif; ?>
  <div id="msg" class="alert err" style="display:none"></div>

  <form id="deteccionUser" method="post" action="index.php" novalidate>
    <?= Csrf::campo() ?>
    <input type="hidden" name="tolog" value="true">
    <label for="usuario">Usuario</label>
    <input id="usuario" name="usuario" type="text" minlength="4" autocomplete="username" autofocus>
    <label for="contrasena">Contraseña</label>
    <input id="contrasena" name="contrasena" type="password" autocomplete="current-password">
    <button type="submit">Entrar</button>
  </form>
  <p class="links">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
</main>
<script>
document.getElementById('deteccionUser').addEventListener('submit', function(e){
  var u=this.usuario.value.trim(), p=this.contrasena.value, box=document.getElementById('msg'), m=[];
  if(u.length<4) m.push('El usuario debe tener al menos 4 caracteres.');
  if(!p) m.push('La contraseña es obligatoria.');
  if(m.length){ e.preventDefault(); box.style.display='block'; box.innerHTML=m.join('<br>'); }
});
</script>
</body></html>
