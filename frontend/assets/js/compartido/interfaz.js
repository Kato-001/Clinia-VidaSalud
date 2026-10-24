/**
 * MediTurno · comportamiento general de la interfaz
 * - Validación de formularios antes de enviar (el servidor valida de nuevo)
 * - Confirmación de acciones, mostrar contraseña, menú lateral en móviles
 */
const MediTurno = (() => {
    'use strict';

    /** Reglas de validación: devuelven el mensaje de error o null */
    const reglas = [
        (c) => (c.required && !c.value.trim()) ? (c.type === 'hidden' && c.dataset.mensaje) || 'Complete este campo.' : null,
        (c) => (c.type === 'email' && c.value && c.validity.typeMismatch) ? 'Escriba un correo válido, por ejemplo nombre@correo.com.' : null,
        (c) => (c.type === 'url' && c.value && c.validity.typeMismatch) ? 'Escriba un enlace que empiece con http:// o https://.' : null,
        (c) => (c.minLength > 0 && c.value && c.value.length < c.minLength) ? `Escriba al menos ${c.minLength} caracteres.` : null,
        (c) => (c.value && c.validity.patternMismatch) ? c.dataset.mensaje || 'El formato no es válido.' : null,
        (c) => (c.value && (c.validity.rangeUnderflow || c.validity.rangeOverflow))
            ? `El valor debe estar entre ${c.min || '…'} y ${c.max || '…'}.` : null,
        (c) => {
            if (!c.dataset.coincide) return null;
            const otro = c.form.elements[c.dataset.coincide];
            return otro && otro.value !== c.value ? 'No coincide con la contraseña escrita.' : null;
        },
        (c) => {
            if (!c.dataset.mayorQue || !c.value) return null;
            const otro = c.form.elements[c.dataset.mayorQue];
            return otro && otro.value && c.value <= otro.value ? 'Debe ser mayor que la hora de inicio.' : null;
        },
    ];

    function contenedorDe(campo) {
        return campo.closest('.campo') || campo.closest('.panel') || campo.parentElement;
    }

    function mostrarError(campo, mensaje) {
        const caja = contenedorDe(campo);
        let nota = caja.querySelector(':scope > .mensaje-error');
        if (!mensaje) {
            if (nota) nota.remove();
            campo.classList.remove('campo-error');
            return;
        }
        if (!nota) {
            nota = document.createElement('small');
            nota.className = 'mensaje-error';
            caja.appendChild(nota);
        }
        nota.textContent = mensaje;
        if (campo.type !== 'hidden') campo.classList.add('campo-error');
    }

    function validarCampo(campo) {
        if (campo.disabled || campo.closest('[hidden]')) return null;
        for (const regla of reglas) {
            const mensaje = regla(campo);
            if (mensaje) return mensaje;
        }
        return null;
    }

    function prepararFormulario(form) {
        const campos = () => [...form.querySelectorAll('input, select, textarea')].filter((c) => c.name);

        campos().forEach((c) => {
            c.addEventListener('change', () => mostrarError(c, validarCampo(c)));
            c.addEventListener('input', () => c.classList.contains('campo-error') && mostrarError(c, validarCampo(c)));
        });

        form.addEventListener('submit', (evento) => {
            let primero = null;
            campos().forEach((c) => {
                const mensaje = validarCampo(c);
                mostrarError(c, mensaje);
                if (mensaje && !primero) primero = c;
            });
            form.querySelectorAll('[data-minimo-uno]').forEach((grupo) => {
                const marcado = grupo.querySelector('input:checked');
                const ancla = grupo.querySelector('input');
                if (ancla) mostrarError(ancla, marcado ? null : grupo.dataset.minimoUno);
                if (!marcado && !primero) primero = ancla;
            });

            if (primero) {
                evento.preventDefault();
                contenedorDe(primero).scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (primero.type !== 'hidden') primero.focus({ preventScroll: true });
                return;
            }
            if (form.dataset.confirmacion && !window.confirm(form.dataset.confirmacion)) {
                evento.preventDefault();
                return;
            }
            const boton = evento.submitter || form.querySelector('[type="submit"]');
            if (boton) {
                boton.disabled = true;
                boton.textContent = 'Procesando…';
            }
        });
    }

    function iniciar() {
        document.querySelectorAll('form[data-validacion]').forEach(prepararFormulario);

        document.querySelectorAll('form[data-confirmacion]:not([data-validacion])').forEach((form) => {
            form.addEventListener('submit', (e) => { if (!window.confirm(form.dataset.confirmacion)) e.preventDefault(); });
        });

        document.querySelectorAll('[data-mostrar-clave]').forEach((boton) => {
            boton.addEventListener('click', () => {
                const campo = document.getElementById(boton.dataset.mostrarClave);
                const oculta = campo.type === 'password';
                campo.type = oculta ? 'text' : 'password';
                boton.textContent = oculta ? 'Ocultar' : 'Ver';
            });
        });

        const lateral = document.getElementById('lateral');
        document.querySelectorAll('[data-abrir-menu]').forEach((b) => b.addEventListener('click', () => lateral && lateral.classList.toggle('abierto')));

        document.querySelectorAll('.aviso-exito').forEach((a) => setTimeout(() => a.classList.add('desvanecer'), 6000));
    }

    /** Escapa texto antes de insertarlo como HTML */
    const escapar = (texto) => String(texto ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    /** GET que devuelve JSON o lanza error */
    async function obtenerJson(url) {
        const respuesta = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!respuesta.ok) throw new Error(`Error ${respuesta.status}`);
        return respuesta.json();
    }

    document.addEventListener('DOMContentLoaded', iniciar);
    return { escapar, obtenerJson, mostrarError };
})();
