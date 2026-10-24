/**
 * CU11 Reprogramar cita: turnos libres del mismo médico para la nueva fecha.
 */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-reprogramar');
    if (!form) return;

    const hora = form.elements.horainicio;
    const fecha = form.elements.fecha;
    const nuevo = form.querySelector('[data-resumen="nuevo"]');

    const turnos = new SelectorTurnos(form.querySelector('#turnos'), {
        urlBloques: form.dataset.urlBloques,
        marcar: form.dataset.actual,
        alElegir: (turno) => {
            hora.value = turno ? turno.inicio : '';
            nuevo.textContent = turno ? `${fecha.value.split('-').reverse().join('/')} · ${turno.inicio} – ${turno.fin}` : '—';
            if (turno) MediTurno.mostrarError(hora, null);
        },
    });

    const cargar = () => turnos.cargar(form.dataset.medico, fecha.value, hora.value || null);
    fecha.addEventListener('change', () => { hora.value = ''; cargar(); });
    cargar();
});
