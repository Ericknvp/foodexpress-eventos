---
version: 1
slug: "index-php"
primary_target: "index.php"
related_targets: []
---

# Surface brief - Consola web FoodExpress

## Scope and visitor mode

- Scope: la consola web del simulador de eventos de FoodExpress (pantalla unica en `index.php`).
- Visitor mode: Operate. La persona en la sala acciona el sistema y lee el resultado; no hay narrativa ni conversion.
- Platform: web, servida por Apache/XAMPP en el arbol local de `htdocs`.

## Audience, job, action, proof, constraints

- Audiencia: estudiantes entregando el Taller 1 y la persona que lo evalua; la clase ve la pantalla proyectada.
- Trabajo: emitir uno de los tres eventos y comprobar, en la misma pantalla, a que area interna llego y que quedo escrito en el JSON correspondiente.
- Accion principal: "Emitir evento" sobre uno de los tres escenarios predefinidos.
- Prueba: la traza de la entrega (enrutador, consumidores activados, accion, estado, archivo destino) y el contenido vivo de los tres archivos de `data/`.
- Restricciones: sin emoji, sin comentarios en el codigo PHP, sin dependencias externas ni fuentes remotas, la estructura de `eventos_foodexpress/` no se toca, la web y la CLI comparten `data/`.

## Chosen direction and memorable moment

- Direccion: panel de trabajo claro y sobrio, retícula de construccion visible como armadura, tinta azul de carrier como unico color estructural, un color apagado por area (cocina ambar, logistica azul, atencion verde), cifras tabulares para todo lo que se compara.
- Momento memorable: al emitir un evento, la ficha del evento sale del compositor, pasa por el enrutador y aterriza en la bandeja del area que lo recibio, con su estado escrito; el archivo JSON de destino cambia en la misma pantalla.
- Inercia resuelta: la pantalla no es un tablero de metricas ni un hero con tres tarjetas de icono. Es un lugar de trabajo: compositor a la izquierda, bandejas de trabajo al centro, inspector de archivos a la derecha.

## Direction contract

THESIS: Esta pantalla posee el momento en que un evento cruza del canal hacia un area interna, y el archivo que lo registra. Rechaza el tablero de metricas, el hero de tres tarjetas y la demo de solo lectura: aqui nada reporta, todo acciona, y el registro de la accion queda en pantalla junto a la accion.

OWN-WORLD: Superficies blancas de trabajo sobre un fondo gris claro, filetes finos en gris frio, tinta casi negra con sesion azul para el texto y un unico azul de carrier para la estructura y la accion primaria. Tres marcas de area apagadas: ambar para cocina, azul para logistica, verde para atencion al cliente, usadas solo como canto de 3px, punto y etiqueta, nunca como inundacion. Sans del sistema para prosa y etiquetas, cifras tabulares para todo numero, identificadores y JSON en monoespaciada usada como dato y no como disfraz. Una retícula de construccion de 4px se Transparenta en la cabecera y en la franja de ruteo.

STORY: Quien llega encuentra un banco de trabajo donde se despachan los pedidos de FoodExpress. Elige uno de los tres escenarios que define la guia, pulsa emitir y ve el evento viajar: el enrutador nombra las areas que va a alcanzar, cada area muestra la accion que tomo y el estado que dejo, y el inspector muestra la linea exacta que se anadio al archivo JSON. Los mismos tres archivos que escribe la CLI son los que estan en pantalla, de modo que el navegador y la terminal cuentan una sola historia.

FIRST VIEWPORT: Un banco de trabajo de tres columnas a altura completa, bajo una cabecera de 56px con la marca a la izquierda, los tres canales de entrada y el enrutador al centro como franja de ruteo viva, y el estado de la corrida a la derecha. Columna izquierda de 300px: el compositor con los tres escenarios, cada uno con su canal, su tipo de evento y los campos que carga, y un boton primario. Centro fluido: tres columnas de bandeja, una por area interna, cada una una pila de fichas con consumidor, accion, estado y archivo destino. Columna derecha de 360px: el inspector de archivos con los tres JSON como pestanas y los registros del archivo activo en tabla. La accion principal esta arriba en la columna izquierda, al alcance sin desplazar.

FORM: El tablero de bandejas por consumidor, tercero en la lista propia de estructuras, sacado por el dado como indice 1. El mundo es la disciplina del resguardo de comprobante impreso, tomada como tipografia, paleta, densidad y un estado marcado, mas la retícula de Crouwel como armadura visible. Seed reference: seed:872876a28c84a610.

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance.

## Challenger verdicts

- Split-flap concourse: DECLINADO. Es un instrumento de senales que esta audiencia no opera: seria un disfraz. Conservado: la disciplina de una fila que se reestiliza para mostrar su estado sin romper la retícula. Elevacion: la ficha de trabajo se reestiliza en su lugar.
- Jet-age ticket wallet: COMPETITIVO. Su densidad de resguardo y su "el estado se estampa solo" son reales, pero el carbon y el sello de anulacion son teatrales para una pantalla proyectada. Elevacion: el estado se escribe al lado de la accion, nunca solo en el color.
- Crouwel grid specimen: GANA. Vence a la direccion asignada en claridad de producto: la armadura visible hace legible el fan-out. Adopta como mundo estructural: retícula de construccion tenue, composicion por celdas, tipografia neutra.
- Labanotation: DECLINADO. El tiempo hacia arriba y la simetria bilateral pelean con un tablero de colas.
- Metro typographic tiles: DECLINADO. Es una pantalla publica, no una pantalla de trabajo, y su caja en minuscula recortada es ilegible en un formulario proyectado.
- Convention circle catalog: DECLINADO. Un mosaico de dibujos no tiene verdad de producto para un enrutador de eventos.

## Unresolved decisions

- Sin decisiones pendientes: direccion visual y origen de datos confirmados por la persona.
- El color de cada area esta fijado en la paleta; el tono exacto se ajusta en el build por contraste, no por gusto.
