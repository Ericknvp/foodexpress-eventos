<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'router.php';

const ANCHO = 78;

function escenarioPedidoNuevo(): array
{
    return [
        'tipo' => 'nuevo_pedido',
        'origen' => 'app_movil',
        'id_pedido' => 'FE-1001',
        'cliente' => 'Valentina Rojas',
        'restaurante' => 'SaborBrasa',
        'items' => [
            ['plato' => 'Bandeja paisa', 'cantidad' => 2, 'precio' => 18500],
            ['plato' => 'Jugo de mango', 'cantidad' => 1, 'precio' => 7000],
        ],
        'total' => 44000,
        'fecha' => '2026-10-20 12:05:00',
    ];
}

function escenarioPedidoCancelado(): array
{
    return [
        'tipo' => 'pedido_cancelado',
        'origen' => 'sitio_web',
        'id_pedido' => 'FE-1001',
        'cliente' => 'Valentina Rojas',
        'restaurante' => 'SaborBrasa',
        'motivo' => 'El cliente digito una direccion equivocada y cancelo desde el sitio web',
        'fecha' => '2026-10-20 12:07:30',
    ];
}
