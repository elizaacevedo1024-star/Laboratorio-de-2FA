<?php
require_once __DIR__ . '/SanitizarEntrada.php';
require_once __DIR__ . '/IHash.php';

// Clase del formulario de registro. Cada método tiene una sola responsabilidad.
class RegistroUsuario {
    private array $datos = [];
    private array $errores = [];
    private mod_db $db;
    private IHash $hash;

    public function __construct(array $post, mod_db $db, IHash $hash) {
        $this->db   = $db;
        $this->hash = $hash;
        $this->sanitizar($post);
    }

    private function sanitizar(array $p): void {
        $this->datos = [
            'nombre'   => SanitizarEntrada::limpiarNombre($p['nombre'] ?? ''),
            'apellido' => SanitizarEntrada::limpiarNombre($p['apellido'] ?? ''),
            'usuario'  => SanitizarEntrada::limpiarUsuario($p['usuario'] ?? ''),
            'correo'   => SanitizarEntrada::limpiarEmail($p['correo'] ?? ''),
            'sexo'     => ($p['sexo'] ?? '') === 'F' ? 'F' : 'M',
            // la contraseña no se modifica (solo se hashea)
            'clave'    => (string)($p['clave'] ?? ''),
            'clave2'   => (string)($p['clave2'] ?? ''),
        ];
    }

    public function validarCampos(): bool {
        $d = $this->datos;
        if ($d['nombre'] === '' || $d['apellido'] === '') $this->errores[] = 'Nombre y apellido son obligatorios.';
        if (strlen($d['usuario']) < 4)                    $this->errores[] = 'El usuario debe tener al menos 4 caracteres.';
        if (!SanitizarEntrada::esEmailValido($d['correo'])) $this->errores[] = 'El correo no es válido.';
        if (strlen($d['clave']) < 8)                      $this->errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        if ($d['clave'] !== $d['clave2'])                 $this->errores[] = 'Las contraseñas no coinciden.';
        return empty($this->errores);
    }

    public function validarDuplicados(): bool {
        if ($this->existe('Usuario', $this->datos['usuario'])) $this->errores[] = 'Ese usuario ya está registrado.';
        if ($this->existe('Correo',  $this->datos['correo']))  $this->errores[] = 'Ese correo ya está registrado.';
        return empty($this->errores);
    }

    private function existe(string $campo, string $valor): bool {
        return $this->db->existe('usuarios', $campo, $valor);
    }

    public function guardar(): bool {
        $d = $this->datos;
        return $this->db->insertSeguro('usuarios', [
            'Nombre'    => $d['nombre'],
            'Apellido'  => $d['apellido'],
            'Usuario'   => $d['usuario'],
            'Correo'    => $d['correo'],
            'HashMagic' => $this->hash->generar($d['clave']),
            'Sexo'      => $d['sexo'],
        ]);
    }

    public function registrar(): bool {
        return $this->validarCampos() && $this->validarDuplicados() && $this->guardar();
    }

    public function getErrores(): array { return $this->errores; }
    public function getUsuario(): string { return $this->datos['usuario']; }
    public function getDato(string $k): string { return $this->datos[$k] ?? ''; }
}
