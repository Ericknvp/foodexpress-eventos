<?php

declare(strict_types=1);

require_once __DIR__ . '/eventos_foodexpress/productor.php';

const AREAS_CONSUMIDOR = [
    'cocina' => 'Cocina',
    'logistica' => 'Logística',
    'atencion_cliente' => 'Atención al Cliente',
];

const CANALES_EVENTO = [
    'app_movil' => 'App móvil',
    'sitio_web' => 'Sitio web',
];

const ESCENARIOS = [
    'nuevo_pedido' => [
        'nombre' => 'Pedido nuevo',
        'canal' => 'app_movil',
        'descripcion' => 'Cocina inicia la preparación y logística asigna un repartidor.',
    ],
    'pedido_cancelado' => [
        'nombre' => 'Pedido cancelado',
        'canal' => 'sitio_web',
        'descripcion' => 'Cocina detiene la preparación y logística libera al repartidor.',
    ],
    'consulta_estado_pedido' => [
        'nombre' => 'Consulta de estado',
        'canal' => 'app_movil',
        'descripcion' => 'Solo atención al cliente responde, con el estado real del pedido.',
    ],
];

function escenariosDisponibles(): array
{
    $eventos = escenarios();
    $escenarios = [];

    foreach (ESCENARIOS as $clave => $definicion) {
        $escenarios[$clave] = array_merge($definicion, [
            'clave' => $clave,
            'evento' => $eventos[$clave],
        ]);
    }

    return $escenarios;
}

function rutasDeConsumidor(): array
{
    $rutas = [];

    foreach (RUTAS_CONSUMIDOR as $tipo => $areas) {
        $rutas[$tipo] = array_map(fn (string $area): string => AREAS_CONSUMIDOR[$area] ?? $area, $areas);
    }

    return $rutas;
}

function estadoDeArchivos(): array
{
    $archivos = [];

    foreach (ARCHIVO_POR_EVENTO as $tipo => $nombre) {
        $registros = leerRegistros($tipo);

        $archivos[] = [
            'tipo' => $tipo,
            'nombre' => $nombre,
            'total' => count($registros),
            'registros' => $registros,
        ];
    }

    return $archivos;
}
