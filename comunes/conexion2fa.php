<?php
if (!class_exists('mod_db')) {
    require __DIR__ . '/../clases/mysql.inc.php';
}

function obtenerSecret2FA(string $usuario): ?string {
    $pdo = (new mod_db())->getConexion();
    $st  = $pdo->prepare("SELECT secret_2fa FROM usuarios WHERE Usuario = :u LIMIT 1");
    $st->execute([':u' => $usuario]);
    $secret = $st->fetchColumn();
    return $secret ?: null;
}

function guardarSecret2FA(string $usuario, string $secret): bool {
    $pdo = (new mod_db())->getConexion();
    $st  = $pdo->prepare("UPDATE usuarios SET secret_2fa = :s WHERE Usuario = :u");
    return $st->execute([':s' => $secret, ':u' => $usuario]);
}
