# FoodExpress - Taller 1: Sistema de eventos

Sistema de eventos para **FoodExpress**, una plataforma de domicilios de comida que
recibe eventos desde distintos canales (app movil y sitio web) y los enruta
automaticamente hacia el area interna que debe atenderlos.

La solucion aplica el patron **productor - enrutador - consumidor** trabajado en
clase: el productor emite los eventos, el enrutador los clasifica y los entrega a
los consumidores correctos, y cada consumidor deja registrada su accion en el
archivo JSON que corresponde al tipo de evento.

## Estructura del proyecto

```
eventos_foodexpress/
├── data/
│   ├── pedidos.json
│   ├── cancelaciones.json
│   └── consultas_estado.json
├── router.php
├── productor.php
└── consumidor.php
```

La carpeta `eventos_foodexpress/` conserva exactamente la estructura que pide la
guia. La consola web vive por fuera de ella, para no alterar esa entrega:

```
index.php              consola web (compositor, bandejas, inspector)
api/
├── datos.php          escenario y lectura de los archivos de data/
├── emitir.php         emite un escenario a traves del enrutador
├── estado.php         devuelve el estado de los tres JSON
└── reiniciar.php      vacia los tres JSON
assets/
├── estilos.css        paleta, retícula y componentes
└── app.js             cliente de la consola
```

| Archivo | Responsabilidad |
|---|---|
| `productor.php` | Simula los tres escenarios de negocio, invoca al enrutador y muestra el resultado en consola. |
| `router.php` | Clasifica el tipo de evento, valida sus campos y hace el fan-out hacia los consumidores. |
| `consumidor.php` | Implementa Cocina, Logistica y Atencion al Cliente, y persiste cada accion en `data/`. |
| `index.php` | Consola web: emite eventos y muestra el fan-out, las entregas y el contenido vivo de los JSON. |

## Correspondencia con el ejemplo de clase

| Ejemplo de clase (tienda en linea) | Taller 1 (FoodExpress) | Area interna | Consumidor |
|---|---|---|---|
| `nuevo_pedido` | `nuevo_pedido` | Cocina y Logistica | Cocina, Logistica |
| `devolucion` | `pedido_cancelado` | Cocina y Logistica | Cocina, Logistica |
| `pregunta_stock` | `consulta_estado_pedido` | Atencion al Cliente | Atencion al Cliente |
| Gestion de Almacen | Cocina / Restaurante | | |
| Sistema Financiero | Logistica / Repartidores | | |
| Logistica | Atencion al Cliente | | |

## Reglas del enrutamiento

| Evento | Consumidores activados | Fan-out |
|---|---|---|
| `nuevo_pedido` | Cocina, Logistica | 2 |
| `pedido_cancelado` | Cocina, Logistica | 2 |
| `consulta_estado_pedido` | Atencion al Cliente | 1 |

## Datos de cada evento

| Evento | Campos |
|---|---|
| `nuevo_pedido` | `id_pedido`, `cliente`, `restaurante`, `items` (lista de platos), `total` |
| `pedido_cancelado` | `id_pedido`, `cliente`, `restaurante`, `motivo` |
| `consulta_estado_pedido` | `cliente`, `id_pedido` |

## Requisitos

- PHP 8.0 o superior (sin dependencias externas, sin base de datos).
- Permiso de escritura en `eventos_foodexpress/data/`.

## Ejecucion

### En el navegador (consola web)

El proyecto vive dentro de `htdocs` de XAMPP, asi que con Apache encendido la
consola se abre en:

```
http://localhost/PedidosComida/
```

Desde ahi se elige un escenario, se pulsa **Emitir** y se ve el evento viajar
desde el canal hasta el area interna que lo recibe, con la accion que cada
consumidor ejecuto y el archivo JSON donde quedo registrada. La web y la linea
de comandos escriben sobre los mismos archivos de `data/`.

La consola tiene tres zonas y nada mas:

| Zona | Que muestra |
|---|---|
| **Escenarios** | Los tres eventos de la guia, el canal de entrada y a que areas los manda el enrutador. |
| **Entregas por area interna** | Una columna por area (Cocina, Logistica, Atencion al Cliente) con la accion que ejecuto y el archivo donde quedo escrita. |
| **Archivos de data/** | El JSON del archivo seleccionado, tal cual queda en disco. |

### En la linea de comandos

```bash
cd eventos_foodexpress
php productor.php
```

La corrida reinicia los tres archivos de `data/` para que el resultado sea
reproducible, emite los tres escenarios y deja el detalle de cada entrega en
consola junto con el resumen final de la simulacion.

## Comportamiento de los consumidores

| Consumidor | Evento | Accion | Estado |
|---|---|---|---|
| Cocina | `nuevo_pedido` | `inicio_preparacion` | `preparacion_en_curso` |
| Cocina | `pedido_cancelado` | `detener_preparacion` | `preparacion_detenida` |
| Logistica | `nuevo_pedido` | `asignar_repartidor` | `repartidor_asignado` |
| Logistica | `pedido_cancelado` | `liberar_repartidor` | `repartidor_liberado` |
| Atencion al Cliente | `consulta_estado_pedido` | `responder_estado_pedido` | estado real del pedido |

Las decisiones se toman sobre el estado real registrado en `data/`: la cocina solo
detiene una preparacion en curso, la logistica solo libera un repartidor que ya
estaba asignado y atencion al cliente reconstruye la trayectoria del pedido
(creado, asignado, cancelado) para responder con el estado correcto.

## Formato de los registros

Cada archivo de `data/` guarda un arreglo JSON con un registro por accion de
consumidor:

```json
{
    "consumidor": "cocina",
    "evento": "nuevo_pedido",
    "accion": "inicio_preparacion",
    "estado": "preparacion_en_curso",
    "detalle": "Comanda recibida en cocina: 2 plato(s) para preparar.",
    "registrado_en": "2026-10-20 15:49:03",
    "origen": "app_movil",
    "fecha": "2026-10-20 12:05:00",
    "id_pedido": "FE-1001",
    "cliente": "Valentina Rojas",
    "restaurante": "SaborBrasa",
    "items": [],
    "total": 44000
}
```

## Evidencia de ejecucion

En la carpeta `evidencia/` se encuentra la salida de la ultima corrida y las
capturas de la consola y de los tres archivos JSON generados:

| Archivo | Contenido |
|---|---|
| `evidencia/salida_consola.txt` | Salida completa de `php productor.php`. |
| `evidencia/captura_salida_consola.png` | Captura de la salida en consola. |
| `evidencia/captura_pedidos.json.png` | Captura de `data/pedidos.json`. |
| `evidencia/captura_cancelaciones.json.png` | Captura de `data/cancelaciones.json`. |
| `evidencia/captura_consultas_estado.json.png` | Captura de `data/consultas_estado.json`. |
| `evidencia/captura_consola_web.png` | Captura de la consola web en escritorio. |
| `evidencia/captura_consola_web_movil.png` | Captura de la consola web en movil. |
