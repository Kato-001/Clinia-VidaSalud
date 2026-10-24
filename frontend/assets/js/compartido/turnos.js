/**
 * MediTurno · componente de turnos (CU9 Consultar disponibilidad)
 * Lo usan el portal público, la reserva de fichas (CU10) y la reprogramación (CU11).
 *
 *   const turnos = new SelectorTurnos(contenedor, { urlBloques, soloLectura, alElegir });
 *   turnos.cargar(idMedico, '2026-10-26');
 */
class SelectorTurnos {
    constructor(contenedor, opciones = {}) {
        this.contenedor = contenedor;
        this.urlBloques = opciones.urlBloques;           // /api/medicos
        this.soloLectura = Boolean(opciones.soloLectura);
        this.alElegir = opciones.alElegir || (() => {});
        this.marcar = opciones.marcar || null;            // 'AAAA-MM-DD HH:MM' resaltado (cita actual)
        this.peticion = 0;

        if (!this.soloLectura) {
            contenedor.addEventListener('click', (e) => {
                const boton = e.target.closest('button.turno');
                if (boton && !boton.disabled) this.elegir(boton);
            });
        }
    }

    mensaje(texto) {
        this.contenedor.innerHTML = `<p class="nota">${MediTurno.escapar(texto)}</p>`;
    }

    limpiar(texto = 'Elija médico y fecha para ver los turnos.') {
        this.alElegir(null);
        this.mensaje(texto);
    }

    async cargar(idMedico, fecha, preseleccion = null) {
        if (!idMedico || !fecha) return this.limpiar();
        const numero = ++this.peticion;
        this.mensaje('Buscando turnos disponibles…');
        try {
            const datos = await MediTurno.obtenerJson(`${this.urlBloques}/${idMedico}/bloques?fecha=${encodeURIComponent(fecha)}`);
            if (numero !== this.peticion) return; // llegó una respuesta más nueva
            this.alElegir(null);
            if (!datos.bloques.length) return this.mensaje(datos.mensaje || 'No hay turnos para esa fecha.');
            this.dibujar(datos.bloques, fecha);
            if (preseleccion) {
                const boton = this.contenedor.querySelector(`button.turno[data-inicio="${preseleccion}"]:not([disabled])`);
                if (boton) this.elegir(boton);
            }
        } catch (e) {
            if (numero === this.peticion) this.mensaje('No se pudo consultar la disponibilidad. Intente nuevamente.');
        }
    }

    dibujar(bloques, fecha) {
        const libres = bloques.filter((b) => b.disponible).length;
        const etiqueta = this.soloLectura ? 'span' : 'button';
        const turnos = bloques.map((b) => {
            const actual = this.marcar === `${fecha} ${b.inicio}`;
            const titulo = actual ? 'Horario actual de la cita' : b.disponible ? `De ${b.inicio} a ${b.fin}` : 'No disponible';
            const atributos = this.soloLectura
                ? `class="turno${b.disponible ? '' : ' ocupado'}"`
                : `type="button" class="turno" data-inicio="${b.inicio}" data-fin="${b.fin}" ${b.disponible ? '' : 'disabled'}`;
            return `<${etiqueta} ${atributos} title="${titulo}">${b.inicio}</${etiqueta}>`;
        }).join('');

        this.contenedor.innerHTML = `
            <div class="turnos-leyenda"><span>${libres} libre(s)</span><span class="no">ocupado o pasado</span></div>
            <div class="turnos" role="${this.soloLectura ? 'list' : 'radiogroup'}">${turnos}</div>`;
    }

    elegir(boton) {
        this.contenedor.querySelectorAll('.turno.elegido').forEach((t) => t.classList.remove('elegido'));
        boton.classList.add('elegido');
        this.alElegir({ inicio: boton.dataset.inicio, fin: boton.dataset.fin });
    }
}

/** Carga en un <select> los médicos activos de una especialidad: [{id, nombre, dias}] */
async function cargarMedicosDeEspecialidad(select, urlMedicos, idEspecialidad, seleccionado = '') {
    select.disabled = true;
    if (!idEspecialidad) {
        select.innerHTML = '<option value="">Primero elija la especialidad</option>';
        return [];
    }
    select.innerHTML = '<option value="">Cargando médicos…</option>';
    const medicos = await MediTurno.obtenerJson(`${urlMedicos}/${idEspecialidad}/medicos`);
    select.innerHTML = '<option value="">Seleccione un médico…</option>' + medicos
        .map((m) => `<option value="${m.id}" data-dias="${MediTurno.escapar(m.dias.join(', '))}" ${String(m.id) === String(seleccionado) ? 'selected' : ''}>${MediTurno.escapar(m.nombre)}</option>`)
        .join('');
    select.disabled = medicos.length === 0;
    if (!medicos.length) select.innerHTML = '<option value="">Sin médicos disponibles</option>';
    return medicos;
}
