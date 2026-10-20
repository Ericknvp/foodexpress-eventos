# FoodExpress · Taller 1: Sistema de eventos

FoodExpress es una plataforma de domicilios de comida que recibe eventos desde
distintos canales y debe enrutarlos hacia el area interna que los atiende. Este
taller reproduce ese sistema con el patron **productor, enrutador, consumidor**.

## Estructura

```
eventos_foodexpress/        el taller, con la estructura que pide la guia
├── data/
│   ├── pedidos.json
│   ├── cancelaciones.json
│   └── consultas_estado.json
├── router.php
├── productor.php
└── consumidor.php

index.php                    la consola web
api.php                      un solo endpoint: ?accion=emitir|estado|reiniciar
datos.php                    escenarios, rutas y lectura de los JSON
assets/                      estilos.css y app.js
evidencia/                   capturas de la terminal y de los tres JSON
```

## Como se ejecuta

**En el navegador.** El proyecto esta dentro de `htdocs` de XAMPP, asi que con
Apache encendido:

```
http://localhost/PedidosComida/
```

La consola tiene tres zonas: los **escenarios** que se pueden emitir, las
**entregas por area interna** (Cocina, Logistica, Atencion al Cliente) y el
**JSON del archivo** que se esta mirando. Al emitir un evento, la entrega
aparece en la columna del area que lo recibio y el JSON cambia en pantalla.

**En la terminal.**

```bash
cd eventos_foodexpress
php productor.php
```

La corrida reinicia los tres archivos de `data/`, emite los tres escenarios y
muestra por pantalla la ruta de cada evento y la accion de cada consumidor.

Ambas superficies usan el mismo enrutador y los mismos consumidores, y escriben
sobre los mismos archivos de `data/`.

## Ruteo

| Evento | Canal | Consumidores | Archivo donde se registra |
|---|---|---|---|
| `nuevo_pedido` | app movil | Cocina y Logistica | `data/pedidos.json` |
| `pedido_cancelado` | sitio web | Cocina y Logistica | `data/cancelaciones.json` |
| `consulta_estado_pedido` | app movil | Atencion al Cliente | `data/consultas_estado.json` |

Los consumidores deciden con el estado real que hay en `data/`, no con valores
fijos: la cocina solo detiene una preparacion en curso, la logistica solo libera
un repartidor que ya estaba asignado y atencion al cliente reconstruye la
trayectoria del pedido antes de responder.

## Evidencia

En `evidencia/` esta la salida de la terminal (`salida_consola.txt`) y las
capturas de esa salida y de los tres archivos JSON, mas las capturas de la
consola web en escritorio y en movil.

## Requisitos

PHP 8.0 o superior, sin dependencias externas y sin base de datos. El taller es
un proyecto academico: los datos son ficticios y no hay pagos ni repartidores
reales.
