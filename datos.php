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
        'evento' => 'escenarioPedidoNuevo',
    ],
    'pedido_cancelado' => [
        'nombre' => 'Pedido cancelado',
        'canal' => 'sitio_web',
        'descripcion' => 'Cocina detiene la preparación y logística libera al repartidor.',
        'evento' => 'escenarioPedidoCancelado',
    ],
    'consulta_estado_pedido' => [
        'nombre' => 'Consulta de estado',
        'canal' => 'app_movil',
        'descripcion' => 'Solo atención al cliente responde, con la trayectoria real del pedido.',
        'evento' => 'escenarioConsultaEstado',
    ],
];

function escenariosDisponibles(): array
{
    $escenarios = [];

    foreach (ESCENARIOS as $clave => $definicion) {
        $escenarios[$clave] = [
            'clave' => $clave,
            'nombre' => $definicion['nombre'],
            'canal' => $definicion['canal'],
            'descripcion' => $definicion['descripcion'],
            'evento' => $definicion['evento'](),
        ];
    }

    return $escenarios;
}

function rutasDeConsumidor(): array
{
    $rutas = [];

    foreach (RUTAS_CONSUMIDOR as $tipo => $destinos) {
        $rutas[$tipo] = array_map(
            fn (string $destino): string => AREAS_CONSUMIDOR[$destino] ?? $destino,
            $destinos
        );
    }

    return $rutas;
}

function estadoDeArchivos(): array
{
    $archivos = [];

    foreach (ARCHIVO_POR_EVENTO as $tipo => $archivo) {
        $registros = leerRegistros($tipo);

        $archivos[] = [
            'tipo' => $tipo,
            'archivo' => 'data/' . $archivo,
            'nombre' => $archivo,
            'total' => count($registros),
            'registros' => $registros,
        ];
    }

    return $archivos;
}
