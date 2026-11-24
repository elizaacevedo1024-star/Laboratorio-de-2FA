<?php
// Muestra en pantalla los privilegios de los usuarios de BD creados (SHOW GRANTS)
require __DIR__ . '/comunes/bloque_Seguridad.php';
$cfg = require __DIR__ . '/clases/config.php';

function grants(array $cfg, string $user, string $pass): array {
    try {
        $pdo = new PDO("mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4", $user, $pass,
                       [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return $pdo->query('SHOW GRANTS')->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return ['No se pudo consultar: ' . $e->getMessage()];
    }
}
$usuarios = [
    $cfg['user']    => grants($cfg, $cfg['user'], $cfg['pass']),
    $cfg['ro_user'] => grants($cfg, $cfg['ro_user'], $cfg['ro_pass']),
];
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Privilegios de la base de datos</title><link rel="stylesheet" href="css/auth.css"></head>
<body class="auth-body">
<main class="card card-wide">
  <h1>Privilegios mínimos de la BD</h1>
  <p class="sub">Comando usado: <code>SHOW GRANTS FOR 'usuario'@'localhost';</code> &mdash; no se utiliza el súper usuario <b>root</b>.</p>
  <?php foreach ($usuarios as $u => $lista): ?>
    <h2><?= htmlspecialchars($u) ?>@localhost</h2>
    <pre class="grants"><?php foreach ($lista as $l) echo htmlspecialchars($l) . "\n"; ?></pre>
  <?php endforeach; ?>
  <p><a href="formularios/PanelControl.php">&larr; Volver al panel</a></p>
</main></body></html>
