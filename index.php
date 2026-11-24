<?PHP
session_start();
require_once "clases/mysql.inc.php";
require_once "clases/SanitizarEntrada.php";
require_once "clases/Csrf.php";
require_once "comunes/loginfunciones.php";
require_once "clases/objLoginAdmin.php";
require_once "comunes/conexion2fa.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['tolog'] ?? '') !== 'true') {
    header("Location: login.php"); exit;
}

// 1) Anti-CSRF
if (!Csrf::validar($_POST['csrf_token'] ?? null)) {
    $_SESSION['emsg'] = 2;
    header("Location: login.php"); exit;
}

$db = new mod_db();
$Logearme = new ValidacionLogin($_POST['usuario'] ?? '', $_POST['contrasena'] ?? '', $_SERVER['REMOTE_ADDR'], $db);

if ($Logearme->logger()) {
    $Logearme->autenticar();
    $Logearme->registrarIntentos();

    if ($Logearme->getIntentoLogin()) {
        $usuarioOk = $Logearme->getUsuario();
        session_regenerate_id(true);
        Csrf::renovar();

        if (obtenerSecret2FA($usuarioOk)) {
            // Contraseña correcta, pero todavía NO entra: se solicita el código QR/TOTP
            $_SESSION['2fa_pendiente'] = true;
            $_SESSION['2fa_usuario']   = $usuarioOk;
            header("Location: verificar_2fa.php"); exit;
        }
        // Aún no tiene 2FA: debe escanear el QR antes de entrar
        $_SESSION['qr_usuario'] = $usuarioOk;
        $_SESSION['qr_origen']  = 'login';
        header("Location: activar_2fa.php"); exit;
    }
}
$_SESSION['emsg'] = 1;
header("Location: login.php"); exit;
