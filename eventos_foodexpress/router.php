<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'consumidor.php';

const RUTAS_CONSUMIDOR = [
    'nuevo_pedido' => ['cocina', 'logistica'],
    'pedido_cancelado' => ['cocina', 'logistica'],
    'consulta_estado_pedido' => ['atencion_cliente'],
];

const CAMPOS_OBLIGATORIOS = [
    'nuevo_pedido' => ['id_pedido', 'cliente', 'restaurante', 'items', 'total'],
    'pedido_cancelado' => ['id_pedido', 'cliente', 'restaurante', 'motivo'],
    'consulta_estado_pedido' => ['cliente', 'id_pedido'],
];

function enrutar(array $evento): array
{
    $tipo = $evento['tipo'] ?? '';

    if (!array_key_exists($tipo, RUTAS_CONSUMIDOR)) {
        throw new InvalidArgumentException("El enrutador no conoce el evento: {$tipo}");
    }

    $faltantes = [];

    foreach (CAMPOS_OBLIGATORIOS[$tipo] as $campo) {
        if (!isset($evento[$campo]) || $evento[$campo] === '' || $evento[$campo] === []) {
            $faltantes[] = $campo;
        }
    }

    if ($faltantes !== []) {
        throw new InvalidArgumentException(
            "El evento {$tipo} esta incompleto, faltan: " . implode(', ', $faltantes)
        );
    }

    $resultados = [];

    foreach (RUTAS_CONSUMIDOR[$tipo] as $area) {
        $registro = consumir($area, $evento);

        $resultados[] = [
            'consumidor' => $area,
            'nombre' => NOMBRE_CONSUMIDOR[$area],
            'evento' => $tipo,
            'accion' => $registro['accion'],
            'estado' => $registro['estado'],
            'detalle' => $registro['detalle'],
            'archivo' => ARCHIVO_POR_EVENTO[$tipo],
        ];
    }

    $destinos = array_map(fn (string $area): string => NOMBRE_CONSUMIDOR[$area], RUTAS_CONSUMIDOR[$tipo]);

    return [
        'tipo' => $tipo,
        'origen' => $evento['origen'] ?? 'desconocido',
        'ruta' => $tipo . ' -> ' . implode(', ', $destinos),
        'destinos' => RUTAS_CONSUMIDOR[$tipo],
        'resultados' => $resultados,
    ];
}
