<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/comunes/conexion2fa.php';
require __DIR__ . '/clases/Csrf.php';
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

if (empty($_SESSION['2fa_pendiente']) || empty($_SESSION['2fa_usuario'])) {
    header('Location: login.php'); exit;
}

$g = new GoogleAuthenticator();
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validar($_POST['csrf_token'] ?? null)) {
        $mensaje = 'Token de seguridad inválido.';
    } else {
        $_SESSION['2fa_intentos'] = ($_SESSION['2fa_intentos'] ?? 0) + 1;
        if ($_SESSION['2fa_intentos'] > 5) { session_destroy(); header('Location: login.php'); exit; }

        $usuario = $_SESSION['2fa_usuario'];
        $secret  = obtenerSecret2FA($usuario);
        $codigo  = trim($_POST['codigo_2fa'] ?? '');

        if ($secret && $g->checkCode($secret, $codigo)) {
            // Código correcto: se crea una NUEVA sesión que confirma que la fase del QR fue exitosa
            session_regenerate_id(true);
            unset($_SESSION['2fa_pendiente'], $_SESSION['2fa_usuario'], $_SESSION['2fa_intentos']);
            $_SESSION['autenticado']    = 'SI';
            $_SESSION['Usuario']        = $usuario;
            $_SESSION['2fa_verificado'] = true;
            header('Location: formularios/PanelControl.php'); exit;
        }
        $mensaje = 'Código incorrecto.';
    }
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verificación 2FA</title><link rel="stylesheet" href="css/auth.css"></head>
<body class="auth-body">
<main class="card">
  <div class="brand"><img src="patria/5564844.png" alt=""> UTP &middot; Desarrollo Web</div>
  <h1>Verificación en dos pasos</h1>
  <p class="sub">Escribe el código de 6 dígitos de Google Authenticator.</p>
  <?php if ($mensaje): ?><div class="alert err"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
  <form method="post" novalidate>
    <?= Csrf::campo() ?>
    <label for="codigo_2fa">Código</label>
    <input id="codigo_2fa" name="codigo_2fa" maxlength="6" pattern="\d{6}" inputmode="numeric" required autofocus>
    <button type="submit">Verificar</button>
  </form>
  <p class="links"><a href="salir.php">Cancelar</a></p>
</main></body></html>
