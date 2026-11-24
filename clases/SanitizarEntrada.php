<?php
class SanitizarEntrada {

    // Quita espacios y etiquetas HTML (ya existía, NO cambiar su comportamiento)
    public static function limpiarCadena($cadena) {
        return trim(strip_tags($cadena));
    }

    // Nombres y apellidos: solo letras (con tildes y ñ), espacios, guion y apóstrofo
    public static function limpiarNombre($cadena) {
        $cadena = self::limpiarCadena($cadena);
        return preg_replace("/[^\p{L}\s'\-]/u", '', $cadena);
    }

    // Correo: minúsculas y sin caracteres no permitidos en un email
    public static function limpiarEmail($cadena) {
        return filter_var(strtolower(trim($cadena)), FILTER_SANITIZE_EMAIL);
    }

    // Usuario: solo letras, números, punto, guion y guion bajo
    public static function limpiarUsuario($cadena) {
        return preg_replace('/[^A-Za-z0-9._\-]/', '', trim($cadena));
    }

    // Enteros (ids, edades, etc.)
    public static function limpiarEntero($valor) {
        return (int) filter_var($valor, FILTER_SANITIZE_NUMBER_INT);
    }

    // Para imprimir datos en HTML y evitar XSS
    public static function escaparHtml($cadena) {
        return htmlspecialchars((string)$cadena, ENT_QUOTES, 'UTF-8');
    }

    // Validación (no modifica): ¿es un correo válido?
    public static function esEmailValido($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}