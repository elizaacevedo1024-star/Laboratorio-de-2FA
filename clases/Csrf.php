<?php
// Protección Anti-CSRF: token aleatorio en sesión + campo oculto en el formulario.
// Al recibir el POST se comparan ambos tokens con hash_equals().
class Csrf {
    public static function token(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function campo(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validar(?string $recibido): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        return !empty($_SESSION['csrf_token']) && is_string($recibido)
            && hash_equals($_SESSION['csrf_token'], $recibido);
    }

    // Renueva el token (después de un POST válido)
    public static function renovar(): void {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
