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

function escenarioConsultaEstado(): array
{
    return [
        'tipo' => 'consulta_estado_pedido',
        'origen' => 'app_movil',
        'id_pedido' => 'FE-1001',
        'cliente' => 'Valentina Rojas',
        'fecha' => '2026-10-20 12:09:10',
    ];
}

function linea(string $caracter = '-'): string
{
    return str_repeat($caracter, ANCHO);
}

function titulo(string $texto): void
{
    echo linea('=') . PHP_EOL;
    echo ' ' . $texto . PHP_EOL;
    echo linea('=') . PHP_EOL;
}

function seccion(string $texto): void
{
    echo PHP_EOL . $texto . PHP_EOL;
    echo linea('-') . PHP_EOL;
}

function campo(string $etiqueta, string $valor, int $largo = 14): void
{
    echo ' ' . str_pad($etiqueta, $largo, '.') . ' ' . $valor . PHP_EOL;
}

function dinero(int $valor): string
{
    return '$ ' . number_format($valor, 0, ',', '.');
}

function envolver(string $texto, int $largo = 58): string
{
    return str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', 16), wordwrap($texto, $largo, PHP_EOL, true));
}

function renderItems(array $items): void
{
    echo ' ' . str_pad('items', 14, '.') . PHP_EOL;

    foreach (array_values($items) as $indice => $item) {
        $subtotal = $item['cantidad'] * $item['precio'];
        $etiqueta = ($indice + 1) . '. ' . $item['plato'];

        echo '   ' . str_pad($etiqueta, 30, '.') . ' '
            . $item['cantidad'] . ' x ' . str_pad(dinero($item['precio']), 12, ' ', STR_PAD_LEFT)
            . ' = ' . dinero($subtotal) . PHP_EOL;
    }
}

function renderEvento(array $evento): void
{
    campo('Evento', $evento['tipo']);
    campo('Canal', $evento['origen']);
    campo('Fecha', $evento['fecha']);
    campo('id_pedido', $evento['id_pedido']);
    campo('cliente', $evento['cliente']);

    if ($evento['tipo'] === 'nuevo_pedido') {
        campo('restaurante', $evento['restaurante']);
        renderItems($evento['items']);
        campo('total', dinero((int) $evento['total']));

        return;
    }

    if ($evento['tipo'] === 'pedido_cancelado') {
        campo('restaurante', $evento['restaurante']);
        campo('motivo', envolver($evento['motivo']));

        return;
    }
}

function renderResultado(array $resultado): void
{
    echo PHP_EOL . ' ' . strtoupper($resultado['nombre']) . PHP_EOL;
    campo('accion', $resultado['accion']);
    campo('estado', $resultado['estado']);
    campo('detalle', envolver($resultado['detalle']));
    campo('registro', 'data/' . $resultado['archivo']);
}
