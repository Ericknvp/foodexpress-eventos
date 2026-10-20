const AREAS = ['cocina', 'logistica', 'atencion_cliente'];

const NOMBRES_AREA = {
    cocina: 'Cocina',
    logistica: 'Logística',
    atencion_cliente: 'Atención al Cliente',
};

const elementos = {
    aviso: document.getElementById('aviso'),
    reiniciar: document.getElementById('reiniciar'),
    jsonBruto: document.getElementById('json-bruto'),
    rutaArchivo: document.getElementById('ruta-archivo'),
    panelArchivo: document.getElementById('panel-archivo'),
    pestanas: Array.from(document.querySelectorAll('.pestana')),
};

let archivos = [];
let archivoActivo = 'nuevo_pedido';

function nombreArchivoDe(tipo) {
    const encontrado = archivos.find((archivo) => archivo.tipo === tipo);

    return encontrado ? encontrado.nombre : '';
}

function mostrarAviso(texto, tono) {
    elementos.aviso.textContent = texto;
    elementos.aviso.dataset.tono = tono || 'info';
    elementos.aviso.classList.add('visible');

    window.clearTimeout(mostrarAviso.temporizador);
    mostrarAviso.temporizador = window.setTimeout(() => {
        elementos.aviso.classList.remove('visible');
    }, 3000);
}

function todosLosRegistros() {
    return archivos.flatMap((archivo) => archivo.registros);
}

function crearFicha(registro) {
    const ficha = document.createElement('li');
    const evento = document.createElement('p');
    const accion = document.createElement('p');
    const estado = document.createElement('p');
    const pie = document.createElement('p');

    ficha.className = 'ficha';
    evento.className = 'ficha-evento';
    evento.textContent = registro.evento;

    accion.className = 'ficha-accion';
    accion.textContent = registro.accion;

    estado.className = 'ficha-estado';
    estado.textContent = registro.estado;

    if (registro.estado === 'cancelado') {
        estado.dataset.alarma = 'si';
    }

    pie.className = 'ficha-pie';
    pie.textContent = 'data/' + nombreArchivoDe(registro.evento);

    ficha.append(evento, accion, estado, pie);

    return ficha;
}

function pintarAreas() {
    AREAS.forEach((area) => {
        const pila = document.querySelector('[data-pila="' + area + '"]');
        const entregas = todosLosRegistros().filter((registro) => registro.consumidor === area);

        pila.innerHTML = '';

        if (entregas.length === 0) {
            const vacia = document.createElement('li');

            vacia.className = 'bandeja-vacia';
            vacia.textContent = 'Sin entregas todavía.';
            pila.appendChild(vacia);
        } else {
            entregas.forEach((registro) => pila.appendChild(crearFicha(registro)));
        }

        document.querySelector('[data-conteo="' + area + '"]').textContent = String(entregas.length);
    });
}

function pintarArchivo() {
    const archivo = archivos.find((item) => item.tipo === archivoActivo) || archivos[0];

    elementos.jsonBruto.textContent = JSON.stringify(archivo.registros, null, 2);
    elementos.rutaArchivo.textContent = 'data/' + archivo.nombre;
}

function activarPestana(pestana) {
    archivoActivo = pestana.dataset.archivo;

    elementos.pestanas.forEach((otra) => {
        const activa = otra === pestana;

        otra.setAttribute('aria-selected', activa ? 'true' : 'false');
        otra.tabIndex = activa ? 0 : -1;
    });

    elementos.panelArchivo.setAttribute('aria-labelledby', pestana.id);

    pintarArchivo();
}

async function emitir(clave, boton) {
    boton.disabled = true;
    boton.textContent = 'Emitiendo';

    try {
        const respuesta = await fetch('api.php?accion=emitir', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ escenario: clave }),
        });

        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(datos.error || 'No se pudo emitir el evento');
        }

        archivos = datos.archivos;

        pintarAreas();
        pintarArchivo();

        const tipo = datos.ruta.resultados[0].evento;
        const pestana = elementos.pestanas.find((otra) => otra.dataset.archivo === tipo);

        if (pestana) {
            activarPestana(pestana);
        }

        const areas = datos.ruta.destinos.map((area) => NOMBRES_AREA[area] || area).join(' y ');

        mostrarAviso(datos.ruta.tipo + ' entregado a ' + areas + '.');
    } catch (error) {
        mostrarAviso(error.message, 'error');
    } finally {
        boton.disabled = false;
        boton.textContent = 'Emitir';
    }
}

async function reiniciar() {
    elementos.reiniciar.disabled = true;

    try {
        const respuesta = await fetch('api.php?accion=reiniciar', { method: 'POST' });
        const datos = await respuesta.json();

        archivos = datos.archivos;

        pintarAreas();
        pintarArchivo();

        mostrarAviso('Archivos de data/ reiniciados.');
    } catch (error) {
        mostrarAviso(error.message, 'error');
    } finally {
        elementos.reiniciar.disabled = false;
    }
}

function iniciar() {
    document.querySelectorAll('[data-emitir]').forEach((boton) => {
        boton.addEventListener('click', () => emitir(boton.dataset.emitir, boton));
    });

    elementos.reiniciar.addEventListener('click', reiniciar);

    elementos.pestanas.forEach((pestana) => {
        pestana.addEventListener('click', () => activarPestana(pestana));
    });

    fetch('api.php?accion=estado')
        .then((respuesta) => respuesta.json())
        .then((datos) => {
            archivos = datos.archivos;

            pintarAreas();
            pintarArchivo();
        })
        .catch(() => mostrarAviso('No se pudo leer el estado de los archivos.', 'error'));
}

document.addEventListener('DOMContentLoaded', iniciar);
