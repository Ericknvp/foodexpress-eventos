const AREAS = ['cocina', 'logistica', 'atencion_cliente'];
const ESTADOS_ALARMA = ['cancelado'];

const elementos = {
    franja: document.getElementById('franja'),
    aviso: document.getElementById('aviso'),
    reiniciar: document.getElementById('reiniciar'),
    cifraEventos: document.getElementById('cifra-eventos'),
    cifraEntregas: document.getElementById('cifra-entregas'),
    cuerpoTabla: document.getElementById('cuerpo-tabla'),
    jsonBruto: document.getElementById('json-bruto'),
    rutaArchivo: document.getElementById('ruta-archivo'),
    panelArchivo: document.getElementById('panel-archivo'),
    pestanas: Array.from(document.querySelectorAll('.pestana')),
};

let archivoActivo = 'nuevo_pedido';
let avisoTemporizador = null;
const fichasConocidas = new Set();

function claveFicha(registro) {
    return [registro.consumidor, registro.accion, registro.estado, registro.registrado_en, registro.id_pedido || ''].join('|');
}

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

function mostrarAviso(texto, tono) {    elementos.aviso.textContent = texto;
    elementos.aviso.dataset.tono = tono || 'info';
    elementos.aviso.classList.add('visible');

    window.clearTimeout(avisoTemporizador);
    avisoTemporizador = window.setTimeout(() => {
        elementos.aviso.classList.remove('visible');
    }, 3200);
}

function construirFicha(registro, animar) {
    const ficha = crearElemento('li', 'ficha');
    const entrada = crearElemento('div', 'ficha-entrada');

    entrada.appendChild(crearElemento('span', 'ficha-evento', registro.evento));

    if (registro.id_pedido) {
        entrada.appendChild(crearElemento('span', 'ficha-id', registro.id_pedido));
    }

    ficha.appendChild(entrada);
    ficha.appendChild(crearElemento('p', 'ficha-accion', registro.accion));

    const estado = crearElemento('span', 'ficha-estado', registro.estado);

    if (ESTADOS_ALARMA.includes(registro.estado)) {
        estado.dataset.alarma = 'si';
    }

    ficha.appendChild(estado);

    if (registro.detalle) {
        ficha.appendChild(crearElemento('p', 'ficha-detalle', registro.detalle));
    }

    const pie = crearElemento('div', 'ficha-pie');
    const nombreArchivo = 'data/' + archivoDeTipo(registro.evento);
    pie.appendChild(crearElemento('span', null, nombreArchivo));
    pie.appendChild(crearElemento('time', null, (registro.registrado_en || '').slice(11)));

    ficha.appendChild(pie);

    if (animar) {
        ficha.classList.add('ficha-nueva');
    }

    return ficha;
}

function archivoDeTipo(tipo) {
    const encontrada = estadoArchivos.find((archivo) => archivo.tipo === tipo);

    return encontrada ? encontrada.nombre : '';
}

let estadoArchivos = [];

function poblarBandejas(archivos, animar) {
    AREAS.forEach((area) => {
        const pila = document.querySelector('[data-pila="' + area + '"]');
        const fichas = [];

        archivos.forEach((archivo) => {
            archivo.registros.forEach((registro, posicion) => {
                if (registro.consumidor === area) {
                    fichas.push({ registro: registro, archivo: archivo, posicion: posicion });
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
            const vacia = crearElemento('li', 'bandeja-vacia', 'Sin entregas en esta corrida');
            pila.appendChild(vacia);
        } else {
            fichas.forEach((item) => {
                const clave = claveFicha(item.registro);
                const esNueva = !fichasConocidas.has(clave);
                fichasConocidas.add(clave);
                pila.appendChild(construirFicha(item.registro, esNueva && animar));
            });
        }

        const conteo = document.querySelector('[data-conteo="' + area + '"]');
        conteo.textContent = String(fichas.length);
    });
}

function poblarTabla(archivos) {
    const archivo = archivos.find((item) => item.tipo === archivoActivo) || archivos[0];
    elementos.cuerpoTabla.innerHTML = '';

    if (archivo.registros.length === 0) {
        const fila = crearElemento('tr');
        const celda = crearElemento('td', 'celda-mono', 'El archivo aun no tiene registros.');
        celda.colSpan = 3;
        fila.appendChild(celda);
        elementos.cuerpoTabla.appendChild(fila);
    } else {
        archivo.registros.forEach((registro) => {
            const fila = crearElemento('tr');
            const consumidor = crearElemento('td', 'celda-mono');
            consumidor.appendChild(crearElemento('strong', null, registro.consumidor));
            fila.appendChild(consumidor);
            fila.appendChild(crearElemento('td', 'celda-mono', registro.accion));

            const estado = crearElemento('td', 'celda-estado');
            estado.appendChild(crearElemento('strong', null, registro.estado));
            estado.appendChild(crearElemento('br'));
            estado.appendChild(crearElemento('span', 'celda-hora', registro.registrado_en));
            fila.appendChild(estado);

            elementos.cuerpoTabla.appendChild(fila);
        });
    }

    elementos.jsonBruto.textContent = JSON.stringify(archivo.registros, null, 2);
    elementos.rutaArchivo.textContent = archivo.archivo;
}

function limpiarFranja() {
    elementos.franja.querySelectorAll('.franja-caja').forEach((caja) => {
        caja.classList.remove('activa', 'pulso');
    });
}

function pintarFranja(canal, areas) {
    limpiarFranja();

    const cajaCanal = elementos.franja.querySelector('[data-canal="' + canal + '"]');
    const cajaEnrutador = elementos.franja.querySelector('[data-enrutador]');

    [cajaCanal, cajaEnrutador].forEach((caja) => {
        if (caja) {
            caja.classList.add('activa', 'pulso');
        }
    });

    areas.forEach((area) => {
        const caja = elementos.franja.querySelector('[data-area="' + area + '"]');

        if (caja) {
            caja.classList.add('activa', 'pulso');
        }
    });
}

function actualizarContadores() {
    const tipos = new Set();
    let entregas = 0;

    estadoArchivos.forEach((archivo) => {
        entregas += archivo.registros.length;
        archivo.registros.forEach((registro) => tipos.add(registro.evento));
    });

    elementos.cifraEventos.textContent = String(tipos.size);
    elementos.cifraEntregas.textContent = String(entregas);
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

        pintarFranja(datos.canal, datos.ruta.destinos);
        poblarBandejas(estadoArchivos, true);
        poblarTabla(estadoArchivos);
        actualizarContadores();

        const destino = datos.ruta.resultados[0] ? archivoDeTipo(datos.ruta.resultados[0].evento) : '';
        seleccionarArchivo(destino);

        const destinos = datos.ruta.destinos
            .map((area) => datos.ruta.resultados.find((r) => r.consumidor === area).nombre)
            .join(' y ');

        mostrarAviso(datos.ruta.tipo + ' entregado a ' + destinos + '.');
    } catch (error) {
        mostrarAviso(error.message, 'error');
    } finally {
        boton.disabled = false;
        boton.textContent = boton.dataset.etiqueta;
    }
}

function activarPestana(pestana) {
    archivoActivo = pestana.dataset.archivo;

    elementos.pestanas.forEach((otra) => {
        const activa = otra === pestana;
        otra.setAttribute('aria-selected', activa ? 'true' : 'false');
        otra.tabIndex = activa ? 0 : -1;
    });

    elementos.panelArchivo.setAttribute('aria-labelledby', pestana.id);
    poblarTabla(estadoArchivos);
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

async function reiniciar() {
    elementos.reiniciar.disabled = true;

    try {
        const respuesta = await fetch('api/reiniciar.php', { method: 'POST' });
        const datos = await respuesta.json();

        estadoArchivos = datos.archivos;
        fichasConocidas.clear();
        limpiarFranja();

        poblarBandejas(estadoArchivos, false);
        poblarTabla(estadoArchivos);
        actualizarContadores();
        mostrarAviso('Archivos de data/ reiniciados.');
    } catch (error) {
        mostrarAviso(error.message, 'error');
    } finally {
        elementos.reiniciar.disabled = false;
    }
}

function iniciar() {
    document.querySelectorAll('[data-emitir]').forEach((boton) => {
        boton.dataset.etiqueta = boton.textContent;
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
            poblarBandejas(estadoArchivos, false);
            poblarTabla(estadoArchivos);
            actualizarContadores();
        })
        .catch(() => mostrarAviso('No se pudo leer el estado de los archivos.', 'error'));
}

document.addEventListener('DOMContentLoaded', iniciar);
