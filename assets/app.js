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
    pestanas: Array.from(document.querySelectorAll('.pestana')),
};

let archivoActivo = 'nuevo_pedido';
let eventosEmitidos = 0;
let entregasTotales = 0;
let avisoTemporizador = null;

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

function formatearDinero(valor) {
    return '$ ' + Number(valor).toLocaleString('es-CO');
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

function construirFicha(registro) {
    const ficha = crearElemento('li', 'ficha ficha-nueva');
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

    return ficha;
}

function archivoDeTipo(tipo) {
    const encontrada = estadoArchivos.find((archivo) => archivo.tipo === tipo);

    return encontrada ? encontrada.nombre : '';
}

let estadoArchivos = [];

function poblarBandejas(archivos) {
    AREAS.forEach((area) => {
        const pila = document.querySelector('[data-pila="' + area + '"]');
        const fichas = [];

        archivos.forEach((archivo) => {
            archivo.registros.forEach((registro) => {
                if (registro.consumidor === area) {
                    fichas.push(registro);
                }
            });
        });

        pila.innerHTML = '';

        if (fichas.length === 0) {
            const vacia = crearElemento('li', 'bandeja-vacia', 'Sin entregas en esta corrida');
            pila.appendChild(vacia);
        } else {
            fichas.forEach((registro) => {
                pila.appendChild(construirFicha(registro));
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

            const estado = crearElemento('td', 'celda-mono');
            estado.appendChild(crearElemento('strong', null, registro.estado));
            estado.appendChild(crearElemento('br'));
            estado.appendChild(crearElemento('span', null, registro.registrado_en));
            fila.appendChild(estado);

            elementos.cuerpoTabla.appendChild(fila);
        });
    }

    elementos.jsonBruto.textContent = JSON.stringify(archivo.registros, null, 2);
}

function pintarFranja(canal, areas) {
    elementos.franja.querySelectorAll('.franja-caja').forEach((caja) => {
        caja.classList.remove('activa', 'pulso');
    });

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
    elementos.cifraEventos.textContent = String(eventosEmitidos);
    elementos.cifraEntregas.textContent = String(entregasTotales);
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
        eventosEmitidos += 1;
        entregasTotales += datos.ruta.resultados.length;

        pintarFranja(datos.canal, datos.ruta.destinos);
        poblarBandejas(estadoArchivos);
        poblarTabla(estadoArchivos);
        actualizarContadores();

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

async function reiniciar() {
    elementos.reiniciar.disabled = true;

    try {
        const respuesta = await fetch('api/reiniciar.php', { method: 'POST' });
        const datos = await respuesta.json();

        estadoArchivos = datos.archivos;
        eventosEmitidos = 0;
        entregasTotales = 0;

        poblarBandejas(estadoArchivos);
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
        pestana.addEventListener('click', () => {
            archivoActivo = pestana.dataset.archivo;
            elementos.pestanas.forEach((otra) => {
                otra.setAttribute('aria-selected', otra === pestana ? 'true' : 'false');
            });
            poblarTabla(estadoArchivos);
        });
    });

    fetch('api/estado.php')
        .then((respuesta) => respuesta.json())
        .then((datos) => {
            estadoArchivos = datos.archivos;
            poblarBandejas(estadoArchivos);
            poblarTabla(estadoArchivos);
        })
        .catch(() => mostrarAviso('No se pudo leer el estado de los archivos.', 'error'));
}

document.addEventListener('DOMContentLoaded', iniciar);
