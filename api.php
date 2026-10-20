<?php

/*
api.php
Aqui se maneja el unico endpoint de la consola web. Recibe ?accion=emitir,
?accion=estado o ?accion=reiniciar, llama al enrutador cuando toca emitir un
evento y devuelve el estado de los tres archivos JSON de data/.
*/

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/datos.php';

$accion = $_GET['accion'] ?? 'estado';
$respuesta = [];

if ($accion === 'emitir') {
    $entrada = json_decode((string) file_get_contents('php://input'), true);
    $clave = is_array($entrada) ? ($entrada['escenario'] ?? '') : '';

    if (!array_key_exists($clave, ESCENARIOS)) {
        http_response_code(400);
        echo json_encode(['error' => 'Escenario desconocido: ' . $clave], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $escenario = escenariosDisponibles()[$clave];

    try {
        $ruta = enrutar($escenario['evento']);
    } catch (Throwable $error) {
        http_response_code(500);
        echo json_encode(['error' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $respuesta = [
        'escenario' => $clave,
        'canal' => $escenario['canal'],
        'evento' => $escenario['evento'],
        'ruta' => $ruta,
        'archivos' => estadoDeArchivos(),
    ];
} elseif ($accion === 'reiniciar') {
    reiniciarDatos();
    $respuesta = ['archivos' => estadoDeArchivos()];
} else {
    $respuesta = ['archivos' => estadoDeArchivos()];
}

echo json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
