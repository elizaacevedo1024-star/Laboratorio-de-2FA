<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/comunes/conexion2fa.php';
require __DIR__ . '/comunes/qr.php';
require __DIR__ . '/clases/Csrf.php';
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

// Solo se llega aquí justo después de registrarse o de iniciar sesión sin 2FA activo
$usuario = $_SESSION['qr_usuario'] ?? null;
if (!$usuario) { header('Location: login.php'); exit; }
$origen = $_SESSION['qr_origen'] ?? 'login';

$g = new GoogleAuthenticator();
$mensaje = '';

if (obtenerSecret2FA($usuario)) { // ya tenía 2FA: no se vuelve a mostrar el QR
    unset($_SESSION['qr_usuario']); header('Location: login.php'); exit;
}
if (empty($_SESSION['secret_pendiente'])) {
    $_SESSION['secret_pendiente'] = $g->generateSecret();
}
$secret = $_SESSION['secret_pendiente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validar($_POST['csrf_token'] ?? null)) {
        $mensaje = 'Token de seguridad inválido.';
    } elseif ($g->checkCode($secret, trim($_POST['codigo'] ?? ''))) {
        guardarSecret2FA($usuario, $secret);
        unset($_SESSION['secret_pendiente'], $_SESSION['qr_usuario'], $_SESSION['qr_origen']);
        session_regenerate_id(true);
        if ($origen === 'registro') {
            $_SESSION['ok_msg'] = '2FA activado. Ya puedes iniciar sesión.';
            header('Location: login.php'); exit;
        }
        // Venía de un login correcto: se crea la sesión de acceso (código QR confirmado)
        $_SESSION['autenticado']    = 'SI';
        $_SESSION['Usuario']        = $usuario;
        $_SESSION['2fa_verificado'] = true;
        header('Location: formularios/PanelControl.php'); exit;
    } else {
        $mensaje = 'Código incorrecto, intenta de nuevo.';
    }
}

$app     = 'MiSistema';
$otpauth = 'otpauth://totp/' . rawurlencode($app . ':' . $usuario)
         . '?secret=' . $secret . '&issuer=' . rawurlencode($app);
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Activar 2FA</title><link rel="stylesheet" href="css/auth.css"></head>
<body class="auth-body">
<main class="card">
  <div class="brand"><img src="patria/5564844.png" alt=""> UTP &middot; Desarrollo Web</div>
  <h1>Activa la verificación en dos pasos</h1>
  <p class="sub">1. Abre Google Authenticator &rarr; «+» &rarr; Escanear código QR.</p>
  <?php if ($mensaje): ?><div class="alert err"><?= $h($mensaje) ?></div><?php endif; ?>
  <div class="qrbox"><?= qrSvg($otpauth) ?></div>
  <p class="sub">¿No puedes escanearlo? Clave manual:</p>
  <div class="secret"><?= $h($secret) ?></div>
  <form method="post" novalidate>
    <?= Csrf::campo() ?>
    <label for="codigo">2. Escribe el código de 6 dígitos que muestra la app</label>
    <input id="codigo" name="codigo" maxlength="6" pattern="\d{6}" inputmode="numeric" required autofocus>
    <button type="submit">Confirmar y activar</button>
  </form>
  <p class="links"><a href="salir.php">Cancelar</a></p>
</main></body></html>
