<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/datos.php';

$entrada = json_decode((string) file_get_contents('php://input'), true);
$clave = is_array($entrada) ? ($entrada['escenario'] ?? '') : '';

$definidos = [];

foreach (escenariosDisponibles() as $escenario) {
    $definidos[$escenario['clave']] = $escenario;
}

if (!array_key_exists($clave, $definidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Escenario desconocido: ' . $clave], JSON_UNESCAPED_UNICODE);
    exit;
}

$escenario = $definidos[$clave];

try {
    $ruta = enrutar($escenario['evento']);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'escenario' => $clave,
    'canal' => $escenario['canal'],
    'evento' => $escenario['evento'],
    'ruta' => $ruta,
    'archivos' => estadoDeArchivos(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
