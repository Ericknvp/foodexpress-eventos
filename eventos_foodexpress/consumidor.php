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

const REPARTIDORES = ['Carlos Ramirez', 'Daniela Ortiz', 'Andres Molina'];

function directorioDatos(): string
{
    return __DIR__ . DIRECTORY_SEPARATOR . 'data';
}

function rutaArchivo(string $tipo): string
{
    return directorioDatos() . DIRECTORY_SEPARATOR . ARCHIVO_POR_EVENTO[$tipo];
}

function leerRegistros(string $tipo): array
{
    $ruta = rutaArchivo($tipo);

    if (!is_file($ruta)) {
        return [];
    }

    $datos = json_decode((string) file_get_contents($ruta), true);

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

function pedidoExiste(string $idPedido): bool
{
    foreach (leerRegistros('nuevo_pedido') as $registro) {
        if (($registro['id_pedido'] ?? '') === $idPedido) {
            return true;
        }
    }

    return false;
}

function pedidoCancelado(string $idPedido): bool
{
    foreach (leerRegistros('pedido_cancelado') as $registro) {
        if (($registro['id_pedido'] ?? '') === $idPedido) {
            return true;
        }
    }

    return false;
}

function repartidorDe(string $idPedido): ?string
{
    foreach (leerRegistros('nuevo_pedido') as $registro) {
        if (($registro['consumidor'] ?? '') === 'logistica' && ($registro['id_pedido'] ?? '') === $idPedido) {
            return $registro['repartidor'] ?? null;
        }
    }

    return null;
}

function siguienteRepartidor(): string
{
    $ocupados = [];

    foreach (leerRegistros('nuevo_pedido') as $registro) {
        if (($registro['consumidor'] ?? '') === 'logistica' && !empty($registro['repartidor'])) {
            $ocupados[$registro['repartidor']] = true;
        }
    }

    foreach (REPARTIDORES as $repartidor) {
        if (!isset($ocupados[$repartidor])) {
            return $repartidor;
        }
    }

    return REPARTIDORES[count($ocupados) % count(REPARTIDORES)];
}

function cocina(array $evento): array
{
    if ($evento['tipo'] === 'nuevo_pedido') {
        $platos = count($evento['items'] ?? []);

        return [
            'accion' => 'inicio_preparacion',
            'estado' => 'preparacion_en_curso',
            'detalle' => "Comanda recibida: {$platos} plato(s) para preparar.",
        ];
    }

    if (!pedidoExiste($evento['id_pedido'])) {
        return [
            'accion' => 'sin_preparacion',
            'estado' => 'sin_preparacion',
            'detalle' => 'No habia preparacion activa para ese pedido.',
        ];
    }

    return [
        'accion' => 'detener_preparacion',
        'estado' => 'preparacion_detenida',
        'detalle' => 'La preparacion estaba en curso, se detiene y se libera la mesa.',
    ];
}

function logistica(array $evento): array
{
    if ($evento['tipo'] === 'nuevo_pedido') {
        $repartidor = siguienteRepartidor();

        return [
            'accion' => 'asignar_repartidor',
            'estado' => 'repartidor_asignado',
            'detalle' => "Repartidor {$repartidor} asignado para recoger el pedido.",
            'extra' => ['repartidor' => $repartidor],
        ];
    }

    $repartidor = repartidorDe($evento['id_pedido']);

    if ($repartidor === null) {
        return [
            'accion' => 'sin_repartidor',
            'estado' => 'sin_repartidor_asignado',
            'detalle' => 'No habia repartidor asignado para ese pedido.',
        ];
    }

    return [
        'accion' => 'liberar_repartidor',
        'estado' => 'repartidor_liberado',
        'detalle' => "Repartidor {$repartidor} liberado, queda disponible para otro pedido.",
        'extra' => ['repartidor' => $repartidor],
    ];
}

function atencion_cliente(array $evento): array
{
    $idPedido = $evento['id_pedido'];

    if (!pedidoExiste($idPedido)) {
        return [
            'accion' => 'responder_estado',
            'estado' => 'desconocido',
            'detalle' => "No hay registro del pedido {$idPedido} en la plataforma.",
        ];
    }

    if (pedidoCancelado($idPedido)) {
        return [
            'accion' => 'responder_estado',
            'estado' => 'cancelado',
            'detalle' => "El pedido {$idPedido} quedo cancelado desde el sitio web.",
        ];
    }

    return [
        'accion' => 'responder_estado',
        'estado' => 'en_preparacion',
        'detalle' => "El pedido {$idPedido} esta en preparacion en la cocina.",
    ];
}

function consumir(string $area, array $evento): array
{
    $respuesta = $area($evento);

    $registro = array_merge(
        [
            'consumidor' => $area,
            'evento' => $evento['tipo'],
            'accion' => $respuesta['accion'],
            'estado' => $respuesta['estado'],
            'detalle' => $respuesta['detalle'],
            'id_pedido' => $evento['id_pedido'] ?? '',
            'cliente' => $evento['cliente'] ?? '',
            'registrado_en' => date('Y-m-d H:i:s'),
        ],
        $respuesta['extra'] ?? []
    );

    $registros = leerRegistros($evento['tipo']);
    $registros[] = $registro;
    escribirRegistros($evento['tipo'], $registros);

    return $registro;
}
