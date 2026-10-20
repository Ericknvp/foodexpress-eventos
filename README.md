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
```

## Que simula

Una plataforma de comida recibe pedidos, cancelaciones y consultas de dos
canales (app movil y sitio web). Cada evento tiene que llegar a las areas que
lo atienden y dejar constancia de que lo hicieron. El taller simula ese trafico
con tres eventos y tres areas internas:

| Evento | Canal | Areas que lo reciben | Archivo donde queda escrito |
|---|---|---|---|
| `nuevo_pedido` | app movil | Cocina y Logistica | `data/pedidos.json` |
| `pedido_cancelado` | sitio web | Cocina y Logistica | `data/cancelaciones.json` |
| `consulta_estado_pedido` | app movil | Atencion al Cliente | `data/consultas_estado.json` |

Los datos son ficticios: el pedido `FE-1001` de Valentina Rojas en el restaurante
SaborBrasa es el ejemplo que recorre todo el sistema.

## Como funciona

El recorrido de un evento es siempre el mismo, y son cuatro archivos:

1. **El productor** (`productor.php`) arma el evento con sus datos y lo entrega.
   En la terminal emite los tres escenarios de corrido; en la web, cada boton
   `Emitir` emite uno.
2. **El enrutador** (`router.php`) mira el tipo del evento, comprueba que no le
   falte ningun campo y decide a que areas lo manda. `nuevo_pedido` y
   `pedido_cancelado` van a dos areas (fan-out); `consulta_estado_pedido` va a
   una sola.
3. **Los consumidores** (`consumidor.php`) son Cocina, Logistica y Atencion al
   Cliente. Cada uno reacciona segun su rol de negocio y **escribe lo que hizo
   en el JSON que corresponde al tipo de evento**.
4. Los archivos de `data/` quedan como la unica fuente de verdad: la terminal y
   la web leen y escriben los mismos.

Lo importante es que las reacciones **no estan escritas a mano, se deciden con el
estado real**:

- Cocina solo detiene una preparacion que estaba en curso. Si el pedido ya
  estaba cancelado, responde que ya lo estaba; si nunca se preparo, responde
  que no hay nada que detener.
- Logistica solo libera un repartidor que ya estaba asignado. Si no habia
  repartidor, lo dice.
- Atencion al Cliente reconstruye la trayectoria del pedido (creado, asignado,
  cancelado) y responde con el estado real: `en_transito`, `en_preparacion` o
  `cancelado` con el motivo.

Por eso el mismo evento puede dar respuestas distintas segun lo que haya
pasado antes: si emites el pedido nuevo y luego lo cancelas, cocina detiene la
preparacion y logistica suelta al repartidor; si cancelas un pedido que nunca
llego a preparacion, te diran que no habia nada que parar.

## Como se usa

**En el navegador.** El proyecto esta dentro de `htdocs` de XAMPP, asi que con
Apache encendido:

```
http://localhost/PedidosComida/
```

La consola tiene tres zonas:

| Zona | Que muestra |
|---|---|
| **Escenarios** | Los tres eventos, de que canal entran y a que areas los manda el enrutador. El boton `Emitir` pone el evento en marcha. |
| **Entregas por area interna** | Una columna por area (Cocina, Logistica, Atencion al Cliente). Al emitir, la entrega aparece arriba en la columna del area que la recibio, con la accion que ejecuto, el estado en que la dejo y el archivo donde se escribio. |
| **Archivos de data/** | El JSON del archivo que se este mirando, tal cual queda en disco. Las pestanas cambian de archivo. |

El boton `Reiniciar datos` vacia los tres JSON para empezar otra simulacion desde
cero.

**En la terminal.**

```bash
cd eventos_foodexpress
php productor.php
```

La corrida reinicia los tres archivos de `data/`, emite los tres escenarios y
muestra por pantalla la ruta de cada evento, la accion de cada consumidor y el
resumen final.

## Requisitos

PHP 8.0 o superior, sin dependencias externas y sin base de datos. Es un
proyecto academico: no hay pagos ni repartidores reales, solo la simulacion del
ruteo de eventos.
