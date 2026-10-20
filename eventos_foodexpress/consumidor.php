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

function leerRegistros(string $tipo): array
{
    $ruta = rutaArchivo($tipo);

    if (!is_file($ruta)) {
        return [];
    }

    $contenido = file_get_contents($ruta);

    if ($contenido === false || trim($contenido) === '') {
        return [];
    }

    $datos = json_decode($contenido, true);

    return is_array($datos) ? $datos : [];
}

function escribirRegistros(string $tipo, array $registros): void
{
    $json = json_encode(
        array_values($registros),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    file_put_contents(rutaArchivo($tipo), $json . PHP_EOL, LOCK_EX);
}

function camposDelEvento(string $tipo, array $evento): array
{
    $campos = [];

    foreach (CAMPOS_POR_EVENTO[$tipo] as $campo) {
        if (array_key_exists($campo, $evento)) {
            $campos[$campo] = $evento[$campo];
        }
    }

    return $campos;
}

function registrarAccion(string $tipo, string $consumidor, array $evento, array $accion): array
{
    $registro = array_merge(
        [
            'consumidor' => $consumidor,
            'evento' => $tipo,
            'accion' => $accion['accion'],
            'estado' => $accion['estado'],
            'detalle' => $accion['detalle'],
            'registrado_en' => date('Y-m-d H:i:s'),
        ],
        $accion['campos'] ?? [],
        camposDelEvento($tipo, $evento)
    );

    $registros = leerRegistros($tipo);
    $registros[] = $registro;
    escribirRegistros($tipo, $registros);

    return $registro;
}

function estadoPreparacion(string $idPedido): string
{
    $estado = 'sin_preparacion';

    foreach (['nuevo_pedido', 'pedido_cancelado'] as $tipo) {
        foreach (leerRegistros($tipo) as $registro) {
            if (($registro['consumidor'] ?? '') === 'cocina' && ($registro['id_pedido'] ?? '') === $idPedido) {
                $estado = $registro['estado'];
            }
        }
    }

    return $estado;
}

function cocina(array $evento): array
{
    $tipo = $evento['tipo'];

    if ($tipo === 'nuevo_pedido') {
        $cantidad = count($evento['items'] ?? []);

        return registrarAccion($tipo, 'cocina', $evento, [
            'accion' => 'inicio_preparacion',
            'estado' => 'preparacion_en_curso',
            'detalle' => "Comanda recibida en cocina: {$cantidad} plato(s) para preparar.",
        ]);
    }

    if ($tipo === 'pedido_cancelado') {
        $estadoActual = estadoPreparacion($evento['id_pedido']);

        if ($estadoActual === 'preparacion_en_curso') {
            $accion = 'detener_preparacion';
            $estado = 'preparacion_detenida';
            $detalle = 'La preparacion estaba en curso, se detiene y se libera la mesa.';
        } elseif ($estadoActual === 'plato_listo') {
            $accion = 'cancelacion_no_posible';
            $estado = 'preparacion_completada';
            $detalle = 'El plato ya estaba listo, la cancelacion no aplica sobre la preparacion.';
        } else {
            $accion = 'sin_preparacion_para_cancelar';
            $estado = 'sin_preparacion';
            $detalle = 'No habia preparacion activa para este pedido, no hay nada que detener.';
        }

        return registrarAccion($tipo, 'cocina', $evento, [
            'accion' => $accion,
            'estado' => $estado,
            'detalle' => $detalle,
        ]);
    }

    throw new InvalidArgumentException("El consumidor de cocina no procesa el evento {$tipo}");
}
