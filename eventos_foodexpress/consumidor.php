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

function registrarAccion(
    string $tipo,
    string $area,
    array $evento,
    string $accion,
    string $estado,
    string $detalle,
    array $extra = []
): array {
    $registro = array_merge(
        [
            'consumidor' => $area,
            'evento' => $tipo,
            'accion' => $accion,
            'estado' => $estado,
            'detalle' => $detalle,
            'id_pedido' => $evento['id_pedido'] ?? '',
            'cliente' => $evento['cliente'] ?? '',
            'registrado_en' => date('Y-m-d H:i:s'),
        ],
        $extra
    );

    $registros = leerRegistros($tipo);
    $registros[] = $registro;
    escribirRegistros($tipo, $registros);

    return $registro;
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
    $tipo = $evento['tipo'];

    if ($tipo === 'nuevo_pedido') {
        $platos = count($evento['items'] ?? []);

        return registrarAccion(
            $tipo,
            'cocina',
            $evento,
            'inicio_preparacion',
            'preparacion_en_curso',
            "Comanda recibida: {$platos} plato(s) para preparar."
        );
    }

    if (!pedidoExiste($evento['id_pedido'])) {
        return registrarAccion(
            $tipo,
            'cocina',
            $evento,
            'sin_preparacion',
            'sin_preparacion',
            'No habia preparacion activa para ese pedido.'
        );
    }

    return registrarAccion(
        $tipo,
        'cocina',
        $evento,
        'detener_preparacion',
        'preparacion_detenida',
        'La preparacion estaba en curso, se detiene y se libera la mesa.'
    );
}

function logistica(array $evento): array
{
    $tipo = $evento['tipo'];

    if ($tipo === 'nuevo_pedido') {
        $repartidor = siguienteRepartidor();

        return registrarAccion(
            $tipo,
            'logistica',
            $evento,
            'asignar_repartidor',
            'repartidor_asignado',
            "Repartidor {$repartidor} asignado para recoger el pedido.",
            ['repartidor' => $repartidor]
        );
    }

    $repartidor = repartidorDe($evento['id_pedido']);

    if ($repartidor === null) {
        return registrarAccion(
            $tipo,
            'logistica',
            $evento,
            'sin_repartidor',
            'sin_repartidor_asignado',
            'No habia repartidor asignado para ese pedido.'
        );
    }

    return registrarAccion(
        $tipo,
        'logistica',
        $evento,
        'liberar_repartidor',
        'repartidor_liberado',
        "Repartidor {$repartidor} liberado, queda disponible para otro pedido.",
        ['repartidor' => $repartidor]
    );
}

function atencionCliente(array $evento): array
{
    $tipo = $evento['tipo'];
    $idPedido = $evento['id_pedido'];

    if (!pedidoExiste($idPedido)) {
        return registrarAccion(
            $tipo,
            'atencion_cliente',
            $evento,
            'responder_estado',
            'desconocido',
            "No hay registro del pedido {$idPedido} en la plataforma."
        );
    }

    if (pedidoCancelado($idPedido)) {
        return registrarAccion(
            $tipo,
            'atencion_cliente',
            $evento,
            'responder_estado',
            'cancelado',
            "El pedido {$idPedido} quedo cancelado desde el sitio web."
        );
    }

    return registrarAccion(
        $tipo,
        'atencion_cliente',
        $evento,
        'responder_estado',
        'en_preparacion',
        "El pedido {$idPedido} esta en preparacion en la cocina."
    );
}
