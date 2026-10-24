/**
 * CU9 Consultar disponibilidad (portal público): muestra los turnos libres sin iniciar sesión.
 */
document.addEventListener('DOMContentLoaded', () => {
    const raiz = document.getElementById('consulta-turnos');
    if (!raiz) return;

    const especialidad = raiz.querySelector('[name="especialidad"]');
    const medico = raiz.querySelector('[name="medico"]');
    const fecha = raiz.querySelector('[name="fecha"]');
    const dias = raiz.querySelector('#dias-atencion');
    const turnos = new SelectorTurnos(raiz.querySelector('#turnos'), { urlBloques: raiz.dataset.urlBloques, soloLectura: true });

    especialidad.addEventListener('change', async () => {
        dias.textContent = '';
        turnos.limpiar();
        await cargarMedicosDeEspecialidad(medico, raiz.dataset.urlMedicos, especialidad.value);
    });

    const actualizar = () => {
        const opcion = medico.selectedOptions[0];
        dias.textContent = medico.value ? `Atiende: ${opcion.dataset.dias || 'sin horario publicado'}` : '';
        turnos.cargar(medico.value, fecha.value);
    };
    medico.addEventListener('change', actualizar);
    fecha.addEventListener('change', actualizar);
});
