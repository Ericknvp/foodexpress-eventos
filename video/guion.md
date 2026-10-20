# Guion del video (2 minutos)

Locucion por bloques. Cada bloque indica que se ve en pantalla.

## 1. Presentación

**Pantalla:** Consola web en http://localhost/PedidosComida/ con las tres zonas visibles: Escenarios, Entregas por área interna y Archivos de data/.

**Locucion:** FoodExpress es una plataforma de domicilios de comida. Esta consola simula su sistema de eventos: recibe pedidos de la app móvil y del sitio web, y los reparte a las áreas que los atienden.

`0:00.8  (10.7 s)`

## 2. El patrón productor, enrutador, consumidor

**Pantalla:** Diagrama simple con tres flechas: productor, enrutador, consumidores. Si no hay, la carpeta eventos_foodexpress/ con sus tres archivos.

**Locucion:** El sistema sigue un patrón de productor, enrutador y consumidor. El productor arma el evento. El enrutador lo valida y decide a qué áreas lo envía. Y cada consumidor, Cocina, Logística o Atención al Cliente, reacciona según su rol.

`0:11.8  (15.2 s)`

## 3. La evidencia queda escrita

**Pantalla:** La zona Archivos de data/ con el JSON de pedidos.json.

**Locucion:** Cada consumidor deja por escrito lo que hizo, en un archivo JSON. Esa es la única fuente de verdad: la terminal y la web leen los mismos archivos.

`0:27.3  (8.7 s)`

## 4. Ejemplo en vivo: pedido nuevo

**Pantalla:** Click en Emitir en Pedido nuevo. La ficha aparece arriba en Cocina y en Logística, y el JSON de pedidos.json crece.

**Locucion:** Vamos con un pedido nuevo: el 1001 de Erick Narváez en Sabor Brasa, desde la app móvil. El enrutador lo reparte a dos áreas. Cocina registra que la preparación empieza. Logística asigna a Carlos Ramírez. Un evento, dos entregas.

`0:36.4  (15.4 s)`

## 5. El código del enrutador

**Pantalla:** Editor con eventos_foodexpress/router.php. Resaltar la constante RUTAS_CONSUMIDOR y el foreach de la función enrutar.

**Locucion:** En el código, el enrutador es una tabla de tres líneas que dice a qué áreas va cada evento, y una función que valida el evento antes de repartirlo.

`0:52.2  (7.8 s)`

## 6. El segundo escenario: cancelar

**Pantalla:** Click en Emitir en Pedido cancelado. Las dos columnas se actualizan y el JSON de cancelaciones.json muestra lo escrito.

**Locucion:** Ahora el mismo pedido se cancela desde el sitio web. Y aquí está lo importante: la respuesta no está escrita a mano, se decide con el estado real guardado en los archivos. Como la preparación estaba en curso, Cocina la detiene. Como había un repartidor, Logística lo suelta.

`1:00.3  (16.6 s)`

## 7. El pero

**Pantalla:** El botón Reiniciar datos y luego Emitir solo en Pedido cancelado, sin emitir antes el pedido nuevo. La columna de Cocina responde sin preparación.

**Locucion:** Pero si cancelaras un pedido que nunca llegó a cocina, te dirían que no había nada que detener. El mismo evento, otra respuesta.

`1:17.3  (7.7 s)`

## 8. Consulta de estado

**Pantalla:** Click en Emitir en Consulta de estado. Solo se llena la columna de Atención al Cliente.

**Locucion:** La tercera es una consulta desde la app. Aquí el enrutador es selectivo: va a un solo consumidor. Atención al Cliente reconstruye la trayectoria del pedido y responde con el estado real: en preparación, en tránsito o cancelado.

`1:25.3  (13.6 s)`

## 9. El código del consumidor

**Pantalla:** Editor con eventos_foodexpress/consumidor.php. Resaltar la función cocina() y las tres funciones de área.

**Locucion:** En el consumidor está esa lógica: una función corta por área, que solo decide qué hacer y devuelve la acción, el estado y el detalle.

`1:39.3  (7.7 s)`

## 10. Cómo queda escrito

**Pantalla:** La función consumir() en consumidor.php, luego el JSON con el registro escrito.

**Locucion:** Después, una única función escribe eso en el archivo que corresponde a cada tipo de evento. Ahí queda la constancia de lo que hizo cada área.

`1:47.3  (8.0 s)`

## 11. Terminal y cierre

**Pantalla:** Terminal con php productor.php y su salida resumida. Título o logo de FoodExpress.

**Locucion:** Y también corre en la terminal: el productor reinicia los datos, emite los tres escenarios y muestra el resumen. Sin base de datos ni dependencias: solo PHP y archivos JSON. Gracias.

`1:55.6  (12.1 s)`

