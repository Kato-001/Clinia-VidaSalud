/**
 * CU10 Reservar ficha: paciente (búsqueda solo para recepción), especialidad → médico → fecha → turno.
 * Mantiene actualizado el resumen lateral. El servidor vuelve a validar la disponibilidad.
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-reserva');
    if (!form) return;

    const campo = (nombre) => form.elements[nombre];
    const resumen = (clave, valor) => { const el = form.querySelector(`[data-resumen="${clave}"]`); if (el) el.textContent = valor || '—'; };
    const dias = form.querySelector('#dias-atencion');

    const turnos = new SelectorTurnos(form.querySelector('#turnos'), {
        urlBloques: form.dataset.urlBloques,
        alElegir: (turno) => {
            campo('horainicio').value = turno ? turno.inicio : '';
            resumen('hora', turno ? `${turno.inicio} – ${turno.fin}` : '');
            if (turno) MediTurno.mostrarError(campo('horainicio'), null);
        },
    });

    // 1. Paciente (solo personal de la clínica)
    const buscador = form.querySelector('#buscar-paciente');
    if (buscador) {
        const lista = form.querySelector('#sugerencias');
        let espera;
        buscador.addEventListener('input', () => {
            campo('idpaciente').value = '';
            resumen('paciente', '');
            clearTimeout(espera);
            const texto = buscador.value.trim();
            if (texto.length < 2) { lista.hidden = true; return; }
            espera = setTimeout(async () => {
                try {
                    const pacientes = await MediTurno.obtenerJson(`${form.dataset.urlPacientes}?q=${encodeURIComponent(texto)}`);
                    lista.innerHTML = pacientes.length
                        ? pacientes.map((p) => `<li data-id="${p.id}" data-nombre="${MediTurno.escapar(p.nombre)}"><span>${MediTurno.escapar(p.nombre)}</span><small>CI ${MediTurno.escapar(p.ci)} · ${MediTurno.escapar(p.codigo)}</small></li>`).join('')
                        : '<li class="vacio"><small>No se encontraron pacientes</small></li>';
                    lista.hidden = false;
                } catch (e) {
                    lista.innerHTML = '<li class="vacio"><small>No se pudo buscar</small></li>';
                    lista.hidden = false;
                }
            }, 300);
        });
        lista.addEventListener('click', (e) => {
            const item = e.target.closest('li[data-id]');
            if (!item) return;
            campo('idpaciente').value = item.dataset.id;
            buscador.value = item.dataset.nombre;
            resumen('paciente', item.dataset.nombre);
            lista.hidden = true;
            MediTurno.mostrarError(campo('idpaciente'), null);
        });
        document.addEventListener('click', (e) => { if (!e.target.closest('.buscador')) lista.hidden = true; });
    }

    // 2. Especialidad y médico
    const especialidad = campo('idespecialidad');
    const medico = campo('idmedico');
    const fecha = campo('fecha');

    async function alCambiarEspecialidad(seleccionado = '') {
        resumen('especialidad', especialidad.value ? especialidad.selectedOptions[0].text : '');
        resumen('medico', '');
        dias.textContent = '';
        turnos.limpiar();
        await cargarMedicosDeEspecialidad(medico, form.dataset.urlMedicos, especialidad.value, seleccionado);
        if (seleccionado) alCambiarMedico(form.dataset.horaAnterior);
    }

    function alCambiarMedico(preseleccion = null) {
        const opcion = medico.selectedOptions[0];
        resumen('medico', medico.value ? opcion.text : '');
        dias.textContent = medico.value ? `Atiende: ${opcion.dataset.dias || 'sin horario publicado'}` : '';
        resumen('fecha', fecha.value ? fecha.value.split('-').reverse().join('/') : '');
        turnos.cargar(medico.value, fecha.value, preseleccion);
    }

    especialidad.addEventListener('change', () => alCambiarEspecialidad());
    medico.addEventListener('change', () => alCambiarMedico());
    fecha.addEventListener('change', () => alCambiarMedico());

    // Al volver con errores del servidor se restauran las selecciones
    if (especialidad.value) alCambiarEspecialidad(form.dataset.medicoAnterior || '');
});
