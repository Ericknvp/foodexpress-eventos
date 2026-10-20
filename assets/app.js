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

let archivoActivo = 'nuevo_pedido';
let estadoArchivos = [];
let avisoTemporizador = null;
const fichasConocidas = new Set();

function crearElemento(etiqueta, clase, texto) {
    const nodo = document.createElement(etiqueta);

    if (clase) {
        nodo.className = clase;
    }

    if (texto !== undefined) {
        nodo.textContent = texto;
    }

    return nodo;
}

function claveFicha(registro) {
    return [registro.consumidor, registro.accion, registro.registrado_en].join('|');
}

function mostrarAviso(texto, tono) {
    elementos.aviso.textContent = texto;
    elementos.aviso.dataset.tono = tono || 'info';
    elementos.aviso.classList.add('visible');

    window.clearTimeout(avisoTemporizador);
    avisoTemporizador = window.setTimeout(() => {
        elementos.aviso.classList.remove('visible');
    }, 3200);
}

function nombreArchivoDe(tipo) {
    const encontrado = estadoArchivos.find((archivo) => archivo.tipo === tipo);

    return encontrado ? encontrado.nombre : '';
}

function construirFicha(registro, animar) {
    const ficha = crearElemento('li', 'ficha');

    ficha.appendChild(crearElemento('p', 'ficha-evento', registro.evento));
    ficha.appendChild(crearElemento('p', 'ficha-accion', registro.accion));

    const estado = crearElemento('p', 'ficha-estado', registro.estado);

    if (registro.estado === 'cancelado') {
        estado.dataset.alarma = 'si';
    }

    ficha.appendChild(estado);
    ficha.appendChild(crearElemento('p', 'ficha-pie', 'data/' + nombreArchivoDe(registro.evento)));

    if (animar) {
        ficha.classList.add('ficha-nueva');
    }

    return ficha;
}

function poblarAreas(animar) {
    AREAS.forEach((area) => {
        const pila = document.querySelector('[data-pila="' + area + '"]');
        const fichas = [];

        estadoArchivos.forEach((archivo) => {
            archivo.registros.forEach((registro, posicion) => {
                if (registro.consumidor === area) {
                    fichas.push({ registro: registro, posicion: posicion });
                }
            });
        });

        fichas.sort((a, b) => {
            if (a.registro.registrado_en === b.registro.registrado_en) {
                return b.posicion - a.posicion;
            }

            return a.registro.registrado_en < b.registro.registrado_en ? 1 : -1;
        });

        pila.innerHTML = '';

        if (fichas.length === 0) {
            pila.appendChild(crearElemento('li', 'bandeja-vacia', 'Sin entregas todavía.'));
        } else {
            fichas.forEach((item) => {
                const clave = claveFicha(item.registro);
                const esNueva = !fichasConocidas.has(clave);
                fichasConocidas.add(clave);
                pila.appendChild(construirFicha(item.registro, esNueva && animar));
            });
        }

        document.querySelector('[data-conteo="' + area + '"]').textContent = String(fichas.length);
    });
}

function poblarArchivo() {
    const archivo = estadoArchivos.find((item) => item.tipo === archivoActivo) || estadoArchivos[0];

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
    poblarArchivo();
}

function seleccionarArchivo(nombreArchivo) {
    if (!nombreArchivo) {
        return;
    }

    const pestana = elementos.pestanas.find((otra) => otra.dataset.nombre === nombreArchivo);

    if (pestana) {
        activarPestana(pestana);
    }
}

async function emitir(clave, boton) {
    boton.disabled = true;
    boton.textContent = 'Emitiendo';

    try {
        const respuesta = await fetch('api/emitir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ escenario: clave }),
        });

        const datos = await respuesta.json();

        if (!respuesta.ok) {
            throw new Error(datos.error || 'No se pudo emitir el evento');
        }

        estadoArchivos = datos.archivos;

        poblarAreas(true);
        poblarArchivo();

        const destino = datos.ruta.resultados[0] ? nombreArchivoDe(datos.ruta.resultados[0].evento) : '';
        seleccionarArchivo(destino);

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
        const respuesta = await fetch('api/reiniciar.php', { method: 'POST' });
        const datos = await respuesta.json();

        estadoArchivos = datos.archivos;
        fichasConocidas.clear();

        poblarAreas(false);
        poblarArchivo();
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

    fetch('api/estado.php')
        .then((respuesta) => respuesta.json())
        .then((datos) => {
            estadoArchivos = datos.archivos;
            poblarAreas(false);
            poblarArchivo();
        })
        .catch(() => mostrarAviso('No se pudo leer el estado de los archivos.', 'error'));
}

document.addEventListener('DOMContentLoaded', iniciar);
