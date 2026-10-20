<?php

declare(strict_types=1);

require_once __DIR__ . '/api/datos.php';

$escenarios = escenariosDisponibles();
$archivos = estadoDeArchivos();
$areas = [
    'cocina' => 'Cocina',
    'logistica' => 'Logistica',
    'atencion_cliente' => 'Atencion al Cliente',
];
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FoodExpress | Consola de eventos</title>
    <link rel="stylesheet" href="assets/estilos.css">
</head>
<body>
<header class="barra-superior">
    <div class="marca">
        <span class="marca-nombre">FoodExpress</span>
        <span class="marca-detalle">Consola de eventos</span>
    </div>

    <div class="franja" id="franja" aria-label="Recorrido del evento: canal, enrutador y areas internas">
        <span class="franja-caja franja-caja--canal" data-canal="app_movil">App movil</span>
        <span class="franja-caja franja-caja--canal" data-canal="sitio_web">Sitio web</span>
        <span class="conector" aria-hidden="true"></span>
        <span class="franja-caja franja-caja--enrutador" data-enrutador="1">Enrutador</span>
        <span class="conector" aria-hidden="true"></span>
        <span class="franja-caja franja-caja--cocina" data-area="cocina"><span class="franja-punto"></span>Cocina</span>
        <span class="franja-caja franja-caja--logistica" data-area="logistica"><span class="franja-punto"></span>Logistica</span>
        <span class="franja-caja franja-caja--atencion_cliente" data-area="atencion_cliente"><span class="franja-punto"></span>Atencion al cliente</span>
    </div>

    <div class="corrida">
        <div class="corrida-cifras">
            <span><b id="cifra-eventos">0</b> eventos</span>
            <span><b id="cifra-entregas">0</b> entregas</span>
        </div>
        <button type="button" class="boton boton--secundario" id="reiniciar">Reiniciar datos</button>
    </div>
</header>

<main class="banco">
    <section class="zona compositor" aria-labelledby="titulo-compositor">
        <h2 class="zona-titulo" id="titulo-compositor">Compositor</h2>
        <p class="zona-nota">Elige un escenario y emite el evento. El enrutador decide a que areas internas llega.</p>

        <ol class="escenarios">
            <?php foreach ($escenarios as $escenario): ?>
                <li class="escenario" data-escenario="<?= $escenario['clave'] ?>">
                    <div class="escenario-cabecera">
                        <span class="escenario-canal"><?= CANALES_EVENTO[$escenario['canal']] ?? $escenario['canal'] ?></span>
                        <button type="button" class="boton boton--primario" data-emitir="<?= $escenario['clave'] ?>">Emitir</button>
                    </div>
                    <p class="escenario-tipo"><?= $escenario['clave'] ?></p>
                    <p class="escenario-descripcion"><?= $escenario['descripcion'] ?></p>
                    <dl class="escenario-campos">
                        <?php foreach ($escenario['evento'] as $campo => $valor): ?>
                            <?php if ($campo === 'tipo' || $campo === 'fecha' || $campo === 'origen') {
                                continue;
                            } ?>
                            <dt><?= $campo ?></dt>
                            <dd><?= is_array($valor) ? count($valor) . ' platos' : htmlspecialchars((string) $valor, ENT_QUOTES) ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section class="zona bandejas" aria-labelledby="titulo-bandejas">
        <h2 class="zona-titulo" id="titulo-bandejas">Entregas por area interna</h2>

        <div class="bandejas-tarjetas">
            <?php foreach ($areas as $clave => $nombre): ?>
                <article class="bandeja bandeja--<?= $clave ?>" data-area="<?= $clave ?>">
                    <header class="bandeja-cabecera">
                        <span class="bandeja-punto" aria-hidden="true"></span>
                        <h3><?= $nombre ?></h3>
                        <span class="bandeja-conteo" data-conteo="<?= $clave ?>">0</span>
                    </header>
                    <ul class="bandeja-pila" data-pila="<?= $clave ?>"></ul>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="zona inspector" aria-labelledby="titulo-inspector">
        <h2 class="zona-titulo" id="titulo-inspector">Archivos de datos</h2>

        <div class="pestanas" role="tablist" aria-label="Archivos de eventos">
            <?php foreach ($archivos as $indice => $archivo): ?>
                <button type="button" class="pestana" role="tab" id="pestana-<?= $indice ?>"
                        aria-controls="panel-archivo" aria-selected="<?= $indice === 0 ? 'true' : 'false' ?>"
                        data-archivo="<?= $archivo['tipo'] ?>"><?= pathinfo($archivo['nombre'], PATHINFO_FILENAME) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="inspector-cuerpo">
            <div class="tabla-contenedor" id="panel-archivo" role="tabpanel" aria-labelledby="pestana-0">
                <p class="ruta-archivo mono" id="ruta-archivo">data/pedidos.json</p>
                <table class="tabla">
                    <thead>
                    <tr>
                        <th scope="col">Consumidor</th>
                        <th scope="col">Accion</th>
                        <th scope="col">Estado</th>
                    </tr>
                    </thead>
                    <tbody id="cuerpo-tabla"></tbody>
                </table>
            </div>

            <details class="json-bruto">
                <summary>Ver JSON del archivo</summary>
                <pre id="json-bruto"></pre>
            </details>
        </div>
    </section>
</main>

<div class="aviso" id="aviso" role="status" aria-live="polite"></div>

<script src="assets/app.js"></script>
</body>
</html>
