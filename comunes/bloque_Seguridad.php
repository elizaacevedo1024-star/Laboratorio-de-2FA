<?PHP
// Bloque de seguridad: exige login Y verificación del código 2FA (QR)
session_start();

if (($_SESSION['autenticado'] ?? '') !== "SI" || empty($_SESSION['2fa_verificado'])) {
	session_unset();
	session_destroy();
	header("Location: ../login.php");
	exit();
}

function nvl(&$var, $default = "") {
	return isset($var) ? $var : $default;
}

function Mensajes($id) {
	$m = [1 => "Operación realizada correctamente.", 2 => "No tiene permisos para esa acción."];
	return htmlspecialchars($m[(int)$id] ?? "", ENT_QUOTES, 'UTF-8');
}
?>
