<?php
// Validación en tiempo real (AJAX): ¿el usuario o correo ya existe?
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/clases/mysql.inc.php';
require __DIR__ . '/clases/SanitizarEntrada.php';

$campo = $_GET['campo'] ?? '';
$valor = $_GET['valor'] ?? '';
if ($campo === 'usuario') { $col = 'Usuario'; $valor = SanitizarEntrada::limpiarUsuario($valor); }
elseif ($campo === 'correo') { $col = 'Correo'; $valor = SanitizarEntrada::limpiarEmail($valor); }
else { echo json_encode(['existe' => false]); exit; }

if ($valor === '') { echo json_encode(['existe' => false]); exit; }
echo json_encode(['existe' => (new mod_db())->existe('usuarios', $col, $valor)]);
