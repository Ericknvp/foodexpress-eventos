<?php

/*
productor.php
Aqui se maneja el productor: arma los tres eventos del taller y los entrega
al enrutador. En la linea de comandos corre la simulacion completa e imprime
la salida; desde la consola web se usan solo sus funciones de escenario.
*/

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
    $espacios = max(0, intdiv(ANCHO - strlen($texto), 2));

    echo linea('=') . PHP_EOL;
    echo str_repeat(' ', $espacios) . $texto . PHP_EOL;
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

function dato(string $etiqueta, string $valor): void
{
    echo ' ' . str_pad($etiqueta, 22, '.') . ' ' . $valor . PHP_EOL;
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

        echo '   ' . str_pad($etiqueta, 26, '.') . ' '
            . $item['cantidad'] . ' x ' . dinero($item['precio'])
            . ' = ' . str_pad(dinero($subtotal), 11, ' ', STR_PAD_LEFT) . PHP_EOL;
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

function tabla(array $encabezados, array $filas): void
{
    $anchos = array_map('strlen', $encabezados);

    foreach ($filas as $fila) {
        foreach (array_values($fila) as $indice => $celda) {
            $anchos[$indice] = max($anchos[$indice], strlen((string) $celda));
        }
    }

    $componer = function (array $celdas, bool $mayusculas = false) use ($anchos): string {
        $partes = [];

        foreach (array_values($celdas) as $indice => $celda) {
            $texto = (string) $celda;
            $partes[] = str_pad($mayusculas ? strtoupper($texto) : $texto, $anchos[$indice]);
        }

        return ' ' . rtrim(implode('  ', $partes)) . PHP_EOL;
    };

    echo $componer($encabezados, true);
    echo $componer(array_map(fn (int $ancho): string => str_repeat('-', $ancho), $anchos));

    foreach ($filas as $fila) {
        echo $componer($fila);
    }
}

function reiniciarDatos(): void
{
    foreach (ARCHIVO_POR_EVENTO as $archivo) {
        file_put_contents(directorioDatos() . DIRECTORY_SEPARATOR . $archivo, '[]' . PHP_EOL, LOCK_EX);
    }
}

function encabezadoEscenario(int $numero, string $titulo): void
{
    echo linea('-') . PHP_EOL;
    echo ' ESCENARIO ' . $numero . ' | ' . $titulo . PHP_EOL;
    echo linea('-') . PHP_EOL;
}

function ejecutar(): void
{
    titulo('FOODEXPRESS - SISTEMA DE EVENTOS');
    echo ' Taller 1 | Patron: productor -> enrutador -> consumidor' . PHP_EOL;
    echo ' Corrida: ' . date('Y-m-d H:i:s') . PHP_EOL;
    echo linea('=') . PHP_EOL;

    seccion('PREPARACION DE LA SIMULACION');
    dato('Modo', 'simulacion reproducible');
    dato('Origenes', 'app movil y sitio web');
    dato('Archivos', 'data/pedidos.json');
    echo ' ' . str_repeat(' ', 22) . ' data/cancelaciones.json' . PHP_EOL;
    echo ' ' . str_repeat(' ', 22) . ' data/consultas_estado.json' . PHP_EOL;
    dato('Estado', 'se reinician vacios antes de emitir');

    reiniciarDatos();

    $escenarios = [
        ['titulo' => 'Pedido nuevo desde la app movil', 'evento' => escenarioPedidoNuevo()],
        ['titulo' => 'Pedido cancelado desde el sitio web', 'evento' => escenarioPedidoCancelado()],
        ['titulo' => 'Consulta de estado del pedido desde la app movil', 'evento' => escenarioConsultaEstado()],
    ];

    $resumen = [];

    foreach ($escenarios as $indice => $escenario) {
        $numero = $indice + 1;

        encabezadoEscenario($numero, $escenario['titulo']);
        renderEvento($escenario['evento']);

        $ruta = enrutar($escenario['evento']);

        echo PHP_EOL . ' ENRUTADO  ' . $ruta['ruta'] . PHP_EOL;

        foreach ($ruta['resultados'] as $resultado) {
            renderResultado($resultado);

            $resumen[] = [
                'escenario' => (string) $numero,
                'evento' => $ruta['tipo'],
                'consumidor' => $resultado['nombre'],
                'accion' => $resultado['accion'],
                'archivo' => 'data/' . $resultado['archivo'],
            ];
        }
    }

    renderResumen($resumen, count($escenarios));
}

function renderResumen(array $resumen, int $eventos): void
{
    titulo('RESUMEN DE LA CORRIDA');
    echo ' Entregas realizadas por el enrutador' . PHP_EOL;
    echo linea('-') . PHP_EOL;

    $filas = array_map(
        fn (array $fila): array => [$fila['escenario'], $fila['evento'], $fila['consumidor'], $fila['accion']],
        $resumen
    );

    tabla(['Esc.', 'Evento', 'Consumidor', 'Accion'], $filas);

    echo PHP_EOL . ' ARCHIVOS GENERADOS' . PHP_EOL;
    echo linea('-') . PHP_EOL;

    $total = 0;

    foreach (ARCHIVO_POR_EVENTO as $tipo => $archivo) {
        $registros = leerRegistros($tipo);
        $total += count($registros);
        $etiqueta = count($registros) === 1 ? ' registro' : ' registros';
        $ruta = 'data/' . $archivo;

        campo($ruta, count($registros) . $etiqueta, 28);

        foreach ($resumen as $fila) {
            if ($fila['archivo'] === $ruta) {
                echo '     ' . str_pad($fila['consumidor'], 20, '.') . ' ' . $fila['accion'] . PHP_EOL;
            }
        }
    }

    echo linea('-') . PHP_EOL;
    dato('Eventos emitidos', (string) $eventos);
    dato('Entregas a consumidores', (string) count($resumen));
    dato('Registros persistidos', (string) $total);
    echo linea('=') . PHP_EOL;
}

if (PHP_SAPI === 'cli') {
    try {
        ejecutar();
    } catch (Throwable $error) {
        echo PHP_EOL . ' ERROR EN LA CORRIDA: ' . $error->getMessage() . PHP_EOL;
        exit(1);
    }
}