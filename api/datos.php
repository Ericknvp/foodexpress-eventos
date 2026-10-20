<?php

declare(strict_types=1);

require_once __DIR__ . '/../eventos_foodexpress/productor.php';

const AREAS_CONSUMIDOR = [
    'cocina' => 'Cocina',
    'logistica' => 'Logística',
    'atencion_cliente' => 'Atención al Cliente',
];

const CANALES_EVENTO = [
    'app_movil' => 'App móvil',
    'sitio_web' => 'Sitio web',
];

function escenariosDisponibles(): array
{
    return [
        [
            'clave' => 'nuevo_pedido',
            'nombre' => 'Pedido nuevo',
            'canal' => 'app_movil',
            'descripcion' => 'Un pedido entra por la app móvil. El enrutador lo entrega a Cocina y a Logística.',
            'evento' => escenarioPedidoNuevo(),
        ],
        [
            'clave' => 'pedido_cancelado',
            'nombre' => 'Pedido cancelado',
            'canal' => 'sitio_web',
            'descripcion' => 'El cliente cancela desde el sitio web. Cocina detiene la preparación y Logística suelta al repartidor.',
            'evento' => escenarioPedidoCancelado(),
        ],
        [
            'clave' => 'consulta_estado_pedido',
            'nombre' => 'Consulta de estado',
            'canal' => 'app_movil',
            'descripcion' => 'El cliente consulta su pedido desde la app móvil. Solo responde Atención al Cliente.',
            'evento' => escenarioConsultaEstado(),
        ],
    ];
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
