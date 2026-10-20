# Design

<!-- impeccable:design-schema 1 -->

## Mundo

Panel de trabajo claro, de mesa de despacho, para una consola de operaciones que se
proyecta en un aula con luz ambiente. Nada de escenografía: superficies blancas
sobre un fondo gris frío, filetes finos, tinta casi negra y un único azul de
carrier para la estructura y la acción primaria. Una retícula de construcción de
8px se transparenta en la cabecera como armadura del plano.

## Color

Estrategia: **restrained**, con tres marcas de área que hacen trabajo real.

| Rol | Token | Valor | Uso |
|---|---|---|---|
| Fondo | `--fondo` | `#e9edf2` | El plano de la mesa. |
| Papel | `--papel` | `#ffffff` | Tarjetas, bandejas, inspector. |
| Papel hundido | `--papel-hundido` | `#f5f8fa` | Cabeceras de bandeja, filas alternas, JSON. |
| Tinta | `--tinta` | `#16202b` | Texto de título y acción. 16.5:1 sobre papel. |
| Tinta secundaria | `--tinta-2` | `#485666` | Texto de apoyo. 7.3:1 sobre papel. |
| Tinta terciaria | `--tinta-3` | `#5f6c7a` | Metadatos. 4.9:1 sobre papel. |
| Filete | `--linea` | `#cbd4de` | Separación de superficies. |
| Filete fuerte | `--linea-fuerte` | `#aab7c4` | Bordes de la franja de ruteo. |
| Reticula | `--reticula` | `rgba(22,32,43,.07)` | Armadura de la cabecera. |
| Carrier | `--carrier` | `#1b4b73` | Acción primaria, enrutador, logística. |
| Cocina | `--cocina` | `#8a5b12` | Marca de área. 5.1:1 sobre su propio tinte. |
| Atención | `--atencion` | `#1c6349` | Marca de área. 6.2:1 sobre su propio tinte. |
| Alarma | `--alarma` | `#9e2b2b` | Estado `cancelado`. 6.3:1 sobre su tinte. |

El color nunca decora: identifica área, marca alarma y separa superficies. Las
tres marcas de área aparecen como punto de la cabecera de bandeja, como tinte de
la etiqueta del evento y como texto; nunca inundan una superficie.

## Tipografía

- **Prosa y etiquetas:** `"Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif`.
- **Datos e identificadores:** `ui-monospace, "Cascadia Mono", Consolas, "Courier New", monospace`.
- La monoespaciada se reserva para datos (tipos de evento, acciones, estados, rutas, horas), nunca como disfraz de lo técnico.
- Cifras tabulares (`font-variant-numeric: tabular-nums`) en todo número que se compare: contadores, totales, horas.
- Escala: 15px cuerpo, 14px acción de ficha y título de zona, 12.5px apoyo, 12px mono de datos, 11.5px metadatos. El mínimo legible desde el fondo del aula es 11px.

## Componentes

| Componente | Definición |
|---|---|
| Cabecera | 57px, tres zonas: marca a la izquierda, franja de ruteo centrada en la página, cifras y acción secundaria a la derecha. Retícula visible de fondo. |
| Franja de ruteo | Cajas de 12px con filete fuerte, punto de área, conectores dibujados en CSS. Al emitir, el canal, el enrutador y las áreas destino se rellenan con su color y pulsan una vez. |
| Compositor | Columna de 300px. Cada escenario es una ficha: tipo de evento en monoespaciada, botón `Emitir` en la cabecera, descripción, y los campos reales del evento con sus valores. |
| Bandeja | Una por área interna. Cabecera con punto de área, nombre y conteo; pila de fichas con la entrega más reciente arriba. |
| Ficha de entrega | Etiqueta del evento, id del pedido, acción, estado, detalle y pie con archivo y hora. La más nueva entra con una sola animación de 420ms. |
| Inspector | Pestañas por tipo de evento, la ruta del archivo, una tabla de registros y el JSON crudo plegable. |
| Botones | Primario `carrier` para emitir; secundario con filete para reiniciar. Estado deshabilitado con tinte neutro y texto legible. |

## Movimiento

Un solo momento, el de la entrega: la ficha nueva entra con un desplazamiento
de 8px y un desenfoque de opacidad, con salida exponencial
`cubic-bezier(0.16, 1, 0.3, 1)`, y la franja de ruteo pulsa las cajas implicadas.
Solo las fichas realmente nuevas se animan; las ya presentes nunca quedan
ocultas por el temporizador de la animación. Con `prefers-reduced-motion` todo
se reduce a 0.

## Superficies del navegador

Selección `#cfe0ef` sobre tinta, barras de scroll finas con pulgar `#c6d0da`,
anillo de foco `2px` en carrier con desplazamiento de `2px`, cifras tabulares en
todo dato comparable. No hay campos de entrada en la superficie, así que el caret
y el subrayado no aplican.

## Regla de Responsive

| Ancho | Composición |
|---|---|
| ≥1361px | Tres columnas: compositor 300px, bandejas fluidas, inspector 380px. |
| 861–1360px | Compositor 280px y bandejas; el inspector pasa debajo a todo el ancho. |
| ≤860px | Una sola columna: compositor, bandejas apiladas, inspector debajo. La cabecera se apila en tres filas. |

## Voz

Español del dominio del taller: pedido, cocina, repartidor, cancelación,
consulta de estado. Los nombres de evento, acción y estado van en monoespaciada
porque son identificadores literales del código. La consola web usa español con
tildes; la salida de terminal se mantiene en ASCII porque la consola de Windows
no garantiza la codificación UTF-8 en un aula.

## Limites conocidos

- La medida de prosa de las fichas queda entre 35 y 45 caracteres, por debajo de
  la banda de 65 a 75, porque tres bandejas conviven en el ancho de una pantalla.
  Es un precio consciente del tablero de colas.
- El idioma de la interfaz web difiere de la salida de terminal en las tildes,
  por la restricción de codificación de la consola de Windows.
