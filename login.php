<?PHP
session_start();
// Cierra cualquier sesión autenticada previa, conservando el mensaje de error y el token CSRF
unset($_SESSION['autenticado'], $_SESSION['Usuario'], $_SESSION['2fa_verificado'],
      $_SESSION['2fa_pendiente'], $_SESSION['2fa_usuario'], $_SESSION['qr_usuario']);
include("login_form.php");
?>
