<?php

declare(strict_types=1);

const ARCHIVO_POR_EVENTO = [
    'nuevo_pedido' => 'pedidos.json',
    'pedido_cancelado' => 'cancelaciones.json',
    'consulta_estado_pedido' => 'consultas_estado.json',
];

const NOMBRE_CONSUMIDOR = [
    'cocina' => 'Cocina',
    'logistica' => 'Logistica',
    'atencion_cliente' => 'Atencion al Cliente',
];

const CAMPOS_POR_EVENTO = [
    'nuevo_pedido' => ['origen', 'id_pedido', 'cliente', 'restaurante', 'items', 'total'],
    'pedido_cancelado' => ['origen', 'id_pedido', 'cliente', 'restaurante', 'motivo'],
    'consulta_estado_pedido' => ['origen', 'id_pedido', 'cliente'],
];

function directorioDatos(): string
{
    return __DIR__ . DIRECTORY_SEPARATOR . 'data';
}

function rutaArchivo(string $tipo): string
{
    if (!array_key_exists($tipo, ARCHIVO_POR_EVENTO)) {
        throw new InvalidArgumentException("Tipo de evento sin archivo asignado: {$tipo}");
    }

    return directorioDatos() . DIRECTORY_SEPARATOR . ARCHIVO_POR_EVENTO[$tipo];
}
