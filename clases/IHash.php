<?php
// Interfaz para generar y validar hashes de contraseña
interface IHash {
    public function generar(string $clave): string;
    public function validar(string $clave, string $hash): bool;
}
