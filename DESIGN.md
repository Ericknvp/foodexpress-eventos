# Design

<!-- impeccable:design-schema 1 -->

## Mundo

Panel de trabajo claro, de mesa de despacho, para una consola de operaciones que se
proyecta en un aula con luz ambiente. Nada de escenografía: superficies blancas
sobre un fondo gris frío, filetes finos, tinta casi negra y un único azul de
carrier para la acción primaria. Tres marcas de área, apagadas, identifican Cocina,
Logística y Atención al Cliente. La retícula de construcción y la franja de ruteo
del primer diseño se retiraron: con tres zonas y un filete por columna, la
estructura ya se lee sola.

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
| Cabecera | 57px: marca a la izquierda, boton `Reiniciar datos` a la derecha. Sin franja de ruteo ni contadores: el enrutador se ve en la zona de escenarios y en las columnas. |
| Zona de escenarios | Columna de 320px con los tres eventos. Cada uno muestra su tipo en monoespaciada, el canal de entrada con una flecha y las areas destino, una linea de comportamiento y el boton `Emitir`. |
| Columna de area | Una por area interna, separada de las demas por un filete vertical. Encabezado con punto de color, nombre y conteo; pila de entregas con la mas reciente arriba. |
| Ficha de entrega | Cuatro lineas: tipo de evento, accion, estado y archivo donde se registro. Sin borde, sin sombra, sin detalle duplicado: el detalle ya esta en el JSON. |
| Archivos de data/ | Panel con pestanas por tipo de evento, la ruta del archivo y el JSON crudo tal cual queda en disco. |
| Botones | Primario `carrier` para emitir; secundario con filete para reiniciar. Estado deshabilitado con tinte neutro y texto legible. |

## Movimiento

Un solo momento, el de la entrega: la ficha nueva entra con un desplazamiento
de 6px y un fundido, con salida exponencial `cubic-bezier(0.16, 1, 0.3, 1)`.
Solo las fichas realmente nuevas se animan; las ya presentes nunca quedan
ocultas por el temporizador de la animacion. Con `prefers-reduced-motion` todo
se reduce a 0.

## Superficies del navegador

Seleccion `#cfe0ef` sobre tinta, barras de scroll finas con pulgar `#c6d0da`,
anillo de foco `2px` en carrier con desplazamiento de `2px`, cifras tabulares en
todo numero comparable. No hay campos de entrada en la superficie, asi que el
caret y el subrayado no aplican.

## Regla de Responsive

| Ancho | Composición |
|---|---|
| ≥1361px | Tres columnas: escenarios 320px, entregas fluidas, archivos 360px. |
| 1181–1360px | Escenarios 300px, entregas fluidas con las fichas a 12.5px, archivos 330px. |
| 861–1180px | Escenarios 300px y entregas; los archivos pasan debajo a todo el ancho. |
| ≤860px | Una sola columna: escenarios, areas apiladas, archivos debajo. |

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
