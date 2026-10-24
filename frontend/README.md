# MediTurno · Frontend (vistas y assets)

Interfaz del sistema, con diseño propio de MediTurno: azul clínico y verde salud. La sirve el backend Laravel, así que no tiene instalación aparte.

```
views/
  compartido/
    layouts/      app (sistema con barra superior y menú lateral), publico (portal), acceso (login, registro, errores)
    partials/     icono (íconos SVG), logo, nav-enlace, alertas, estado, estado-cita, paginacion
    dashboard*.blade.php   inicio de recepción/administración y del paciente
  errors/         403, 404, 419 y 500
  P1_SeguridadAcceso/ … P6_Reportes/   una carpeta por caso de uso (CU1 … CU20)
assets/
  css/compartido/mediturno.css         estilos de todo el sistema
  js/compartido/interfaz.js            validación de formularios, confirmaciones, menú móvil
  js/compartido/turnos.js              componente SelectorTurnos (CU9), lo usan CU9, CU10 y CU11
  js/P4_GestionCitas/CU9…/portal-horarios.js · CU10…/reservar-ficha.js · CU11…/reprogramar.js
```

## Convenciones

**Nombres de los campos:** son los de las columnas del diagrama (`idrol`, `fechanacimiento`, `horainicio`…).

**Validación en el navegador:** `interfaz.js` revisa los formularios antes de enviarlos, con mensajes en español, y el servidor vuelve a validar todo. Atributos que usa:

| Atributo | Para qué sirve |
|---|---|
| `data-validacion` | Activa la validación del formulario |
| `data-mensaje` | Mensaje propio cuando el campo no cumple su formato |
| `data-coincide` | El campo debe ser igual a otro (repetir contraseña) |
| `data-mayor-que` | La hora debe ser mayor que la de otro campo |
| `data-minimo-uno` | Un grupo de casillas debe tener al menos una marcada |
| `data-confirmacion` | Pide confirmación antes de enviar |
