<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/datos.php';

reiniciarDatos();

echo json_encode(['archivos' => estadoDeArchivos()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
