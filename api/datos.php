<?php

declare(strict_types=1);

require_once __DIR__ . '/../eventos_foodexpress/productor.php';

const AREAS_CONSUMIDOR = [
    'cocina' => 'Cocina',
    'logistica' => 'Logistica',
    'atencion_cliente' => 'Atencion al Cliente',
];

const CANALES_EVENTO = [
    'app_movil' => 'App movil',
    'sitio_web' => 'Sitio web',
];

function escenariosDisponibles(): array
{
    return [
        [
            'clave' => 'nuevo_pedido',
            'nombre' => 'Pedido nuevo',
            'canal' => 'app_movil',
            'descripcion' => 'Un pedido entra por la app movil. El enrutador lo entrega a cocina y logistica.',
            'evento' => escenarioPedidoNuevo(),
        ],
        [
            'clave' => 'pedido_cancelado',
            'nombre' => 'Pedido cancelado',
            'canal' => 'sitio_web',
            'descripcion' => 'El cliente cancela desde el sitio web. Cocina detiene la preparacion y logistica suelta al repartidor.',
            'evento' => escenarioPedidoCancelado(),
        ],
        [
            'clave' => 'consulta_estado_pedido',
            'nombre' => 'Consulta de estado',
            'canal' => 'app_movil',
            'descripcion' => 'El cliente consulta su pedido desde la app movil. Solo responde atencion al cliente.',
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
