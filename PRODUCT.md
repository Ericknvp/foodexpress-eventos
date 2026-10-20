# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Plain PHP 8 served by Apache/XAMPP, with vanilla CSS and JavaScript in the browser. No framework, no build step, no package manager. The stack is not an open question: the repository already contains PHP sources, the data files are written by PHP, and the deployment target is the XAMPP `htdocs` tree the project lives in.

## Users

- Students of the FoodExpress course delivering Taller 1. They run the code, show it in class and hand it in for evaluation.
- The instructor evaluating the submission, who reads the code, the generated JSON and the evidence of execution.
- A student presenting the workshop from a classroom laptop, with the project projected on a screen in a lit room.

## Product Purpose

Simulate the FoodExpress event system, where a platform of food delivery receives events from different channels and routes each one to the internal area that must handle it, using the producer, router and consumer pattern solved in class.

Success means the same event routed from two channels produces the same routing decision, each consumer reacts according to its business role, every action is persisted in the JSON file that corresponds to the event type, and the whole thing can be shown and understood in the browser as well as from the terminal.

## Positioning

The web console and the CLI share one routing core. The CLI prints the run; the web console lets the same run be triggered, watched and inspected live, with the state of the three JSON files visible without opening an editor. The value is not the drawing, it is that the fan-out, the decision of each consumer and the resulting file write are visible in one place.

## Operating Context

The project is served locally from the XAMPP `htdocs` tree, so the audience is a single machine on a local network, usually offline. Sessions happen in a classroom: the projector is the second screen, the room has daylight, and the demonstration is short and repeatable. The teacher asks for a route that can be opened in a browser and for the console output to be captured as evidence.

## Capabilities and Constraints

- The assignment dictates the directory structure of `eventos_foodexpress/`: `data/pedidos.json`, `data/cancelaciones.json`, `data/consultas_estado.json`, `router.php`, `productor.php`, `consumidor.php`. The web layer lives outside that folder so the graded structure stays untouched.
- The web console and the CLI write to the same `data/` folder, so a single source of truth persists and the evidence of execution stays valid.
- The three events and their fan-out are fixed: `nuevo_pedido` reaches Cocina and Logistica, `pedido_cancelado` reaches Cocina and Logistica, `consulta_estado_pedido` reaches Atencion al Cliente only.
- The consumer decisions are state dependent, not hardcoded: the kitchen only stops a preparation in progress, Logistica only releases a courier that was already assigned, and Atencion al Cliente reconstructs the trajectory of the order before answering.
- No emoji anywhere in the design, and no code comments in the PHP sources. Both are explicit requirements from the brief.
- No external network dependencies: no hosted fonts, no CDN scripts, no remote images. The classroom may be offline.
- PHP 8.0 is the runtime available in the environment.
- The deliverable is a course project, not a real system: there are no real payments, no real couriers, and the data is synthetic.

## Brand Commitments

The product is called FoodExpress and the domain language is the one of the course: pedido, cocina, repartidor, cancelacion, consulta de estado. The interface is written in Spanish. The visual direction is a sober, light panel, chosen for projection in a lit classroom. No emoji, no decorative glyphs standing in for icons.

## Evidence on Hand

- `eventos_foodexpress/data/*.json`: real output of a run, with the records each consumer wrote.
- `evidencia/salida_consola.txt`: the complete console output of the last run.
- `evidencia/captura_*.png`: captures of the console and of the three JSON files.
- The order `FE-1001` is the running example: born on the mobile app, cancelled on the website, then queried from the app.

Data is synthetic and must be labeled as such. There are no real customers, real restaurants, real couriers, real testimonials or performance claims, and future work must not invent them.

## Product Principles

- One routing core, two surfaces. The web console never re-implements the business rules; it calls the same router and consumers as the CLI.
- Show the mechanism, not a claim. The page must let the audience watch an event become a routing decision, a consumer action and a file write.
- One source of truth. The JSON files in `data/` are the state, both for the terminal and for the browser.
- Honest scope. A course simulation, clearly labeled, with synthetic data and no inflated promises.
- Nothing decorative without a job. Every visual element either carries information or gets removed.

## Accessibility & Inclusion

The interface is projected in a lit classroom, so a light ground with strong contrast is a functional requirement, not a preference. Body text and placeholder text hold at least 4.5:1 contrast, large text at least 3:1, focus is always visible for keyboard users, and the layout holds from 390px to wide desktop. Numeric columns use tabular figures so values line up when compared.
