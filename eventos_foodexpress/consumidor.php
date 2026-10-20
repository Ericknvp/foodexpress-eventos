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

const REPARTIDORES = [
    'Carlos Ramirez',
    'Daniela Ortiz',
    'Andres Molina',
];

function siguienteRepartidor(): string
{
    static $indice = 0;

    $repartidor = REPARTIDORES[$indice % count(REPARTIDORES)];
    $indice++;

    return $repartidor;
}

function repartidorAsignado(string $idPedido): ?string
{
    $repartidor = null;

    foreach (leerRegistros('nuevo_pedido') as $registro) {
        if (($registro['consumidor'] ?? '') === 'logistica' && ($registro['id_pedido'] ?? '') === $idPedido) {
            $repartidor = $registro['repartidor'] ?? null;
        }
    }

    foreach (leerRegistros('pedido_cancelado') as $registro) {
        if (($registro['consumidor'] ?? '') === 'logistica' && ($registro['id_pedido'] ?? '') === $idPedido) {
            $repartidor = null;
        }
    }

    return $repartidor;
}

function logistica(array $evento): array
{
    $tipo = $evento['tipo'];

    if ($tipo === 'nuevo_pedido') {
        $repartidor = siguienteRepartidor();

        return registrarAccion($tipo, 'logistica', $evento, [
            'accion' => 'asignar_repartidor',
            'estado' => 'repartidor_asignado',
            'detalle' => "Repartidor {$repartidor} asignado para recoger el pedido en {$evento['restaurante']}.",
            'campos' => ['repartidor' => $repartidor],
        ]);
    }

    if ($tipo === 'pedido_cancelado') {
        $repartidor = repartidorAsignado($evento['id_pedido']);

        if ($repartidor !== null) {
            $accion = 'liberar_repartidor';
            $estado = 'repartidor_liberado';
            $detalle = "Repartidor {$repartidor} liberado, queda disponible para otro pedido.";
            $campos = ['repartidor' => $repartidor];
        } else {
            $accion = 'sin_repartidor_para_liberar';
            $estado = 'sin_repartidor_asignado';
            $detalle = 'No habia repartidor asignado para este pedido, no hay nada que liberar.';
            $campos = [];
        }

        return registrarAccion($tipo, 'logistica', $evento, [
            'accion' => $accion,
            'estado' => $estado,
            'detalle' => $detalle,
            'campos' => $campos,
        ]);
    }

    throw new InvalidArgumentException("El consumidor de logistica no procesa el evento {$tipo}");
}

function trayectoriaPedido(string $idPedido): array
{
    $pasos = [];

    foreach (['nuevo_pedido', 'pedido_cancelado'] as $tipo) {
        foreach (leerRegistros($tipo) as $registro) {
            if (($registro['id_pedido'] ?? '') === $idPedido) {
                $pasos[] = $registro;
            }
        }
    }

    return $pasos;
}

function estadoPedido(string $idPedido): array
{
    $pasos = trayectoriaPedido($idPedido);

    if ($pasos === []) {
        return [
            'estado' => 'desconocido',
            'detalle' => "No se encontro ningun registro del pedido {$idPedido} en la plataforma.",
            'pasos' => 0,
        ];
    }

    $creacion = null;
    $cancelacion = null;
    $repartidor = null;

    foreach ($pasos as $paso) {
        if ($paso['evento'] === 'nuevo_pedido' && $paso['consumidor'] === 'cocina') {
            $creacion = $paso;
        }

        if ($paso['evento'] === 'nuevo_pedido' && $paso['consumidor'] === 'logistica') {
            $repartidor = $paso['repartidor'] ?? null;
        }

        if ($paso['evento'] === 'pedido_cancelado') {
            $cancelacion = $paso;
        }
    }

    if ($cancelacion !== null) {
        return [
            'estado' => 'cancelado',
            'detalle' => "El pedido {$idPedido} quedo cancelado desde el sitio web. Motivo: {$cancelacion['motivo']}. "
                . 'La preparacion se detuvo y el repartidor fue liberado, por lo que no habra entrega.',
            'pasos' => count($pasos),
        ];
    }

    if ($repartidor !== null) {
        return [
            'estado' => 'en_transito',
            'detalle' => "El pedido {$idPedido} esta en camino con el repartidor {$repartidor}.",
            'pasos' => count($pasos),
        ];
    }

    return [
        'estado' => 'en_preparacion',
        'detalle' => "El pedido {$idPedido} esta en preparacion en la cocina de {$creacion['restaurante']}.",
        'pasos' => count($pasos),
    ];
}

function atencionCliente(array $evento): array
{
    $tipo = $evento['tipo'];

    if ($tipo !== 'consulta_estado_pedido') {
        throw new InvalidArgumentException("El consumidor de atencion al cliente no procesa el evento {$tipo}");
    }

    $estado = estadoPedido($evento['id_pedido']);

    return registrarAccion($tipo, 'atencion_cliente', $evento, [
        'accion' => 'responder_estado_pedido',
        'estado' => $estado['estado'],
        'detalle' => $estado['detalle'],
        'campos' => ['pasos_consultados' => $estado['pasos']],
    ]);
}
