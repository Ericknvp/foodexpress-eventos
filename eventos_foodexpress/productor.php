<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'router.php';

function escenarioPedidoNuevo(): array
{
    return [
        'tipo' => 'nuevo_pedido',
        'origen' => 'app_movil',
        'id_pedido' => 'FE-1001',
        'cliente' => 'Erick Narváez',
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
        'cliente' => 'Erick Narváez',
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
        'cliente' => 'Erick Narváez',
        'fecha' => '2026-10-20 12:09:10',
    ];
}

function escenarios(): array
{
    return [
        'nuevo_pedido' => escenarioPedidoNuevo(),
        'pedido_cancelado' => escenarioPedidoCancelado(),
        'consulta_estado_pedido' => escenarioConsultaEstado(),
    ];
}

function reiniciarDatos(): void
{
    foreach (ARCHIVO_POR_EVENTO as $archivo) {
        file_put_contents(directorioDatos() . DIRECTORY_SEPARATOR . $archivo, '[]' . PHP_EOL, LOCK_EX);
    }
}

function correrEnTerminal(): void
{
    echo 'FoodExpress - sistema de eventos' . PHP_EOL;
    echo 'patron: productor -> enrutador -> consumidor' . PHP_EOL;
    echo str_repeat('-', 60) . PHP_EOL;

    reiniciarDatos();

    $entregas = 0;

    foreach (escenarios() as $evento) {
        $ruta = enrutar($evento);

        echo PHP_EOL . $ruta['tipo'] . '  (' . $ruta['origen'] . ')' . PHP_EOL;
        echo '  ruta: ' . $ruta['ruta'] . PHP_EOL;

        foreach ($ruta['resultados'] as $resultado) {
            echo '  - ' . $resultado['nombre'] . ': ' . $resultado['accion']
                . ' (' . $resultado['estado'] . ')' . PHP_EOL;
            $entregas++;
        }
    }

    $registros = 0;

    foreach (array_keys(ARCHIVO_POR_EVENTO) as $tipo) {
        $registros += count(leerRegistros($tipo));
    }

    echo PHP_EOL . str_repeat('-', 60) . PHP_EOL;
    echo 'Eventos emitidos: ' . count(escenarios()) . PHP_EOL;
    echo 'Entregas a consumidores: ' . $entregas . PHP_EOL;
    echo 'Registros en data/: ' . $registros . PHP_EOL;
}

$esEntryPoint = PHP_SAPI === 'cli' && realpath($argv[0] ?? '') === __FILE__;

if ($esEntryPoint) {
    try {
        correrEnTerminal();
    } catch (Throwable $error) {
        echo 'ERROR: ' . $error->getMessage() . PHP_EOL;
        exit(1);
    }
}
