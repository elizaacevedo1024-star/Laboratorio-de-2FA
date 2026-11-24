<?PHP
require_once __DIR__ . '/IHash.php';
require_once __DIR__ . '/HashBcrypt.php';

final class ValidacionLogin {

	private $id;
	private $usuario;
	private $contrasena;
	private $hashGuardado;
	private $loginExitoso = 0;
	private $ip;
	private $pdo;
	private $hasher;

	public function __construct($usuario, $contrasena, $ipRemoto, $pdo, ?IHash $hasher = null) {
		// El usuario se sanitiza; la contraseña NO se modifica (solo se compara con su hash)
		$this->usuario    = SanitizarEntrada::limpiarUsuario($usuario);
		$this->contrasena = (string)$contrasena;
		$this->ip         = $ipRemoto;
		$this->pdo        = $pdo;
		$this->hasher     = $hasher ?? new HashBcrypt();
	}

	public function logger() {
		$u = $this->pdo->log($this->usuario);
		if ($u) {
			$this->id           = $u->id;
			$this->hashGuardado = $u->HashMagic;
			return true;
		}
		return false;
	}

	public function autenticar() {
		$this->loginExitoso = $this->hasher->validar($this->contrasena, (string)$this->hashGuardado) ? 1 : 0;
	}

	public function getIntentoLogin() { return $this->loginExitoso; }
	public function getUsuario()      { return $this->usuario; }

	public function registrarIntentos() {
		$this->pdo->insertSeguro("intentos_login", [
			"Usuario"            => $this->usuario,
			"ipRemoto"           => $this->ip,
			"deteccion_anomalia" => (int)$this->loginExitoso,
		]);
	}
}
?>
