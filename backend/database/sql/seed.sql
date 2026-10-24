-- =====================================================================
-- MediTurno · Datos iniciales
-- Usuarios de prueba (cambiar contraseñas en producción):
--   admin     / Admin123*
--   dr.perez  / Medico123*
--   dra.rojas / Medico123*
--   recepcion / Recepcion123*
--   paciente  / Paciente123*
-- =====================================================================

INSERT INTO rol (nombre, descripcion) VALUES
  ('ADMINISTRADOR', 'Gestiona usuarios, catálogos, médicos y configuración'),
  ('MEDICO',        'Atiende citas, registra consultas, receta y solicita estudios'),
  ('RECEPCIONISTA', 'Registra pacientes y programa, reprograma o cancela citas'),
  ('PACIENTE',      'Reserva y gestiona sus citas, consulta su historial');

INSERT INTO estadocita (codigo, nombre, descripcion, ocupahorario) VALUES
  ('PROGRAMADA',   'Programada',   'Cita reservada por el paciente',            TRUE),
  ('CONFIRMADA',   'Confirmada',   'El paciente confirmó su asistencia',        TRUE),
  ('REPROGRAMADA', 'Reprogramada', 'La cita cambió de fecha u hora',            TRUE),
  ('EN_ATENCION',  'En atención',  'El médico está atendiendo la cita',         TRUE),
  ('ATENDIDA',     'Atendida',     'La consulta fue registrada y cerrada',      TRUE),
  ('CANCELADA',    'Cancelada',    'La cita fue cancelada y libera el horario', FALSE),
  ('NO_ASISTIO',   'No asistió',   'El paciente no se presentó',                FALSE);

INSERT INTO configuracion (clave, valor, descripcion) VALUES
  ('NOMBRE_HOSPITAL',       'MediTurno', 'Nombre que se muestra en la aplicación'),
  ('DURACION_CITA_MINUTOS', '15',        'Duración de cada ficha (cita) en minutos'),
  ('HORAS_MIN_CANCELACION', '2',         'Horas mínimas de anticipación para cancelar o reprogramar'),
  ('DIAS_MAX_RESERVA',      '60',        'Días máximos de anticipación para reservar');

-- Usuarios
INSERT INTO usuario (nombre, username, email, password, idrol) VALUES
  ('Administrador del sistema', 'admin', 'admin@mediturno.com',
   '$2y$10$uxMbpFAj2djYivO.PyNWY.xdE3kLYIYjLzQ1W6ZmuZwRvI.X8v0s.', (SELECT idrol FROM rol WHERE nombre = 'ADMINISTRADOR')),
  ('Juan Pérez', 'dr.perez', 'jperez@mediturno.com',
   '$2y$10$wJBOF3WFtfZFoyMzdAd9SOWaUnQ2u/tkM4leHa338K.cvBEy9j2NC', (SELECT idrol FROM rol WHERE nombre = 'MEDICO')),
  ('Ana Rojas', 'dra.rojas', 'arojas@mediturno.com',
   '$2y$10$wJBOF3WFtfZFoyMzdAd9SOWaUnQ2u/tkM4leHa338K.cvBEy9j2NC', (SELECT idrol FROM rol WHERE nombre = 'MEDICO')),
  ('Rosa Mamani', 'recepcion', 'recepcion@mediturno.com',
   '$2y$10$yBRVA1MHAN.ABgzPUtRwnO7w5f/O62kvx9Gl3gkvflU4r7xAqfzjK', (SELECT idrol FROM rol WHERE nombre = 'RECEPCIONISTA')),
  ('María López', 'paciente', 'maria.lopez@correo.com',
   '$2y$10$PRlmIY0fHzmNwYe7i9p3JuAA3K2uTLRPCionjPLgN36AhpkDkueRC', (SELECT idrol FROM rol WHERE nombre = 'PACIENTE'));

-- Catálogos
INSERT INTO especialidad (nombre, descripcion) VALUES
  ('Medicina General', 'Atención primaria y diagnóstico general'),
  ('Pediatría',        'Atención médica de niños y adolescentes'),
  ('Cardiología',      'Enfermedades del corazón y sistema circulatorio'),
  ('Ginecología',      'Salud del sistema reproductor femenino'),
  ('Dermatología',     'Enfermedades de la piel'),
  ('Traumatología',    'Lesiones del sistema musculoesquelético');

INSERT INTO medicamento (nombre, presentacion) VALUES
  ('Paracetamol 500 mg',  'Tabletas'),
  ('Ibuprofeno 400 mg',   'Tabletas'),
  ('Amoxicilina 500 mg',  'Cápsulas'),
  ('Omeprazol 20 mg',     'Cápsulas'),
  ('Loratadina 10 mg',    'Tabletas'),
  ('Salbutamol 100 mcg',  'Inhalador'),
  ('Metformina 850 mg',   'Tabletas'),
  ('Losartán 50 mg',      'Tabletas');

INSERT INTO catalogoestudio (nombre, categoria, descripcion) VALUES
  ('Hemograma completo',     'Laboratorio',   'Recuento de células sanguíneas'),
  ('Glucosa en ayunas',      'Laboratorio',   'Nivel de glucosa en sangre'),
  ('Perfil lipídico',        'Laboratorio',   'Colesterol total, HDL, LDL y triglicéridos'),
  ('Examen general de orina','Laboratorio',   'Análisis físico, químico y microscópico de orina'),
  ('Radiografía de tórax',   'Imagenología',  'Imagen de pulmones y corazón'),
  ('Ecografía abdominal',    'Imagenología',  'Evaluación de órganos abdominales'),
  ('Electrocardiograma',     'Cardiología',   'Registro de la actividad eléctrica del corazón');

-- Médicos de prueba
INSERT INTO medico (codigo, registroprofesional, nombres, apellidos, telefono, email, idusuario) VALUES
  ('MED-0001', 'RP-10234', 'Juan', 'Pérez', '70000001', 'jperez@mediturno.com',
   (SELECT idusuario FROM usuario WHERE username = 'dr.perez')),
  ('MED-0002', 'RP-20871', 'Ana', 'Rojas', '70000002', 'arojas@mediturno.com',
   (SELECT idusuario FROM usuario WHERE username = 'dra.rojas'));

INSERT INTO medicoespecialidad (idmedico, idespecialidad)
SELECT m.idmedico, e.idespecialidad FROM medico m, especialidad e
WHERE (m.codigo = 'MED-0001' AND e.nombre IN ('Medicina General', 'Cardiología'))
   OR (m.codigo = 'MED-0002' AND e.nombre = 'Pediatría');

-- Dr. Pérez: lunes a viernes 08:00-12:00 y 14:00-18:00
INSERT INTO horario (diasemana, horainicio, horafin, idmedico)
SELECT d, h.inicio, h.fin, (SELECT idmedico FROM medico WHERE codigo = 'MED-0001')
FROM generate_series(1, 5) AS d,
     (VALUES (TIME '08:00', TIME '12:00'), (TIME '14:00', TIME '18:00')) AS h(inicio, fin);

-- Dra. Rojas: lunes, miércoles y viernes 09:00-13:00
INSERT INTO horario (diasemana, horainicio, horafin, idmedico)
SELECT d, TIME '09:00', TIME '13:00', (SELECT idmedico FROM medico WHERE codigo = 'MED-0002')
FROM unnest(ARRAY[1, 3, 5]) AS d;

-- Paciente de prueba
INSERT INTO paciente (codigo, ci, nombres, apellidos, fechanacimiento, sexo, telefono, email, direccion, tiposangre, idusuario) VALUES
  ('PAC-000001', '1234567', 'María', 'López', '1990-05-14', 'F', '71111111', 'maria.lopez@correo.com',
   'Av. Principal 123', 'O+', (SELECT idusuario FROM usuario WHERE username = 'paciente'));

INSERT INTO historiaclinica (numero, alergias, idpaciente) VALUES
  ('HC-000001', 'Penicilina', (SELECT idpaciente FROM paciente WHERE codigo = 'PAC-000001'));

INSERT INTO contactoemergencia (nombre, parentesco, telefono, idpaciente) VALUES
  ('Carlos López', 'Hermano', '72222222', (SELECT idpaciente FROM paciente WHERE codigo = 'PAC-000001'));
