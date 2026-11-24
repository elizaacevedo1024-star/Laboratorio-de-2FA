<?php
require_once __DIR__ . '/IHash.php';

// Implementación de IHash con bcrypt
class HashBcrypt implements IHash {
    private int $cost;

    public function __construct(int $cost = 13) { $this->cost = $cost; }

    public function generar(string $clave): string {
        return password_hash($clave, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }

    public function validar(string $clave, string $hash): bool {
        return password_verify($clave, $hash);
    }
}
