<?php

/*
index.php
Aqui se maneja la pagina de la consola web: muestra los escenarios que se
pueden emitir, las entregas por area interna y el JSON del archivo elegido.
*/

declare(strict_types=1);

require_once __DIR__ . '/datos.php';

$escenarios = escenariosDisponibles();
$archivos = estadoDeArchivos();
$rutas = rutasDeConsumidor();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FoodExpress | Consola de eventos</title>
    <link rel="stylesheet" href="assets/estilos.css">
</head>
<body>
<header class="cabecera">
    <div class="marca">
        <span class="marca-nombre">FoodExpress</span>
        <span class="marca-sep" aria-hidden="true">-</span>
        <span class="marca-detalle">Pedidos de comida</span>
    </div>
    <button type="button" class="boton boton--secundario" id="reiniciar">Reiniciar datos</button>
</header>

<main class="banco">
    <section class="compositor" aria-labelledby="titulo-compositor">
        <h2 class="zona-titulo" id="titulo-compositor">Escenarios</h2>
        <p class="zona-nota">Datos ficticios de simulación. Cada emisión pasa por el enrutador.</p>

        <ol class="escenarios">
            <?php foreach ($escenarios as $escenario): ?>
                <li class="escenario">
                    <p class="escenario-tipo"><?= $escenario['clave'] ?></p>
                    <p class="escenario-ruta">
                        <?= CANALES_EVENTO[$escenario['canal']] ?? $escenario['canal'] ?>
                        <span class="flecha" aria-hidden="true"></span>
                        <?= implode(' y ', $rutas[$escenario['clave']]) ?>
                    </p>
                    <p class="escenario-texto"><?= $escenario['descripcion'] ?></p>
                    <button type="button" class="boton boton--primario" data-emitir="<?= $escenario['clave'] ?>">Emitir</button>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section class="bandejas" aria-labelledby="titulo-bandejas">
        <h2 class="zona-titulo" id="titulo-bandejas">Entregas por área interna</h2>

        <div class="areas">
            <?php foreach (AREAS_CONSUMIDOR as $clave => $nombre): ?>
                <section class="area area--<?= $clave ?>" data-area="<?= $clave ?>">
                    <header class="area-cabecera">
                        <h3><?= $nombre ?></h3>
                        <span class="conteo" data-conteo="<?= $clave ?>">0</span>
                    </header>
                    <ul class="area-pila" data-pila="<?= $clave ?>"></ul>
                </section>
            <?php endforeach; ?>
        </div>
    </section>

    <aside class="inspector" aria-labelledby="titulo-inspector">
        <h2 class="zona-titulo" id="titulo-inspector">Archivos de data/</h2>

        <div class="pestanas" role="tablist" aria-label="Tipos de evento">
            <?php foreach ($archivos as $indice => $archivo): ?>
                <button type="button" class="pestana" role="tab" id="pestana-<?= $indice ?>"
                        aria-controls="panel-archivo" aria-selected="<?= $indice === 0 ? 'true' : 'false' ?>"
                        tabindex="<?= $indice === 0 ? '0' : '-1' ?>"
                        data-archivo="<?= $archivo['tipo'] ?>" data-nombre="<?= $archivo['nombre'] ?>"><?= pathinfo($archivo['nombre'], PATHINFO_FILENAME) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="inspector-cuerpo" id="panel-archivo" role="tabpanel" aria-labelledby="pestana-0">
            <p class="ruta-archivo mono" id="ruta-archivo">data/pedidos.json</p>
            <pre class="json" id="json-bruto"></pre>
        </div>
    </aside>
</main>

<div class="aviso" id="aviso" role="status" aria-live="polite"></div>

<script src="assets/app.js"></script>
</body>
</html>
