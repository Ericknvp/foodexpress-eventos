<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'consumidor.php';

const RUTAS_CONSUMIDOR = [
    'nuevo_pedido' => ['cocina', 'logistica'],
    'pedido_cancelado' => ['cocina', 'logistica'],
    'consulta_estado_pedido' => ['atencion_cliente'],
];

const FUNCION_CONSUMIDOR = [
    'cocina' => 'cocina',
    'logistica' => 'logistica',
    'atencion_cliente' => 'atencionCliente',
];

const CAMPOS_OBLIGATORIOS = [
    'nuevo_pedido' => ['id_pedido', 'cliente', 'restaurante', 'items', 'total'],
    'pedido_cancelado' => ['id_pedido', 'cliente', 'restaurante', 'motivo'],
    'consulta_estado_pedido' => ['cliente', 'id_pedido'],
];

function clasificarEvento(array $evento): string
{
    $tipo = $evento['tipo'] ?? '';

    if (!array_key_exists($tipo, RUTAS_CONSUMIDOR)) {
        throw new InvalidArgumentException("Tipo de evento no soportado por el enrutador: {$tipo}");
    }

    return $tipo;
}

function camposFaltantes(string $tipo, array $evento): array
{
    $faltantes = [];

    foreach (CAMPOS_OBLIGATORIOS[$tipo] as $campo) {
        if (!array_key_exists($campo, $evento) || $evento[$campo] === '' || $evento[$campo] === []) {
            $faltantes[] = $campo;
        }
    }

    return $faltantes;
}
