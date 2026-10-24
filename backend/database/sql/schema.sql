-- =====================================================================
-- MediTurno · Esquema de base de datos (PostgreSQL / Supabase)
-- Fuente: Documentacion/Diagrama de Clinica.svg (UML v2, 21 clases)
-- Las claves foráneas se derivan de las relaciones del diagrama.
-- =====================================================================

-- ---------------------------------------------------------------------
-- Seguridad y sistema
-- ---------------------------------------------------------------------
CREATE TABLE rol (
  idrol        INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nombre       VARCHAR(50)  NOT NULL UNIQUE,
  descripcion  VARCHAR(255)
);

-- USUARIO N — 1 ROL (TIENE)
CREATE TABLE usuario (
  idusuario     INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nombre        VARCHAR(150) NOT NULL,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password      VARCHAR(255) NOT NULL,
  estado        VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO',
  ultimoacceso  TIMESTAMPTZ,
  idrol         INTEGER      NOT NULL REFERENCES rol (idrol)
);

-- BITACORA N — 1 USUARIO (REGISTRA)
CREATE TABLE bitacora (
  idbitacora   INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  accion       VARCHAR(50)  NOT NULL,
  modulo       VARCHAR(50)  NOT NULL,
  tabla        VARCHAR(50),
  idregistro   INTEGER,
  descripcion  TEXT,
  ip           VARCHAR(64),
  fecha        DATE         NOT NULL DEFAULT CURRENT_DATE,
  idusuario    INTEGER      NOT NULL REFERENCES usuario (idusuario)
);

CREATE TABLE configuracion (
  clave        VARCHAR(100) PRIMARY KEY,
  valor        VARCHAR(255) NOT NULL,
  descripcion  VARCHAR(255)
);

-- ---------------------------------------------------------------------
-- Médicos y agenda
-- ---------------------------------------------------------------------
CREATE TABLE especialidad (
  idespecialidad  INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nombre          VARCHAR(100) NOT NULL UNIQUE,
  descripcion     VARCHAR(255),
  estado          VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO'
);

-- MEDICO 0..1 — 1 USUARIO (PERTENECE)
CREATE TABLE medico (
  idmedico             INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  codigo               VARCHAR(20)  NOT NULL UNIQUE,
  registroprofesional  VARCHAR(50)  NOT NULL UNIQUE,
  nombres              VARCHAR(100) NOT NULL,
  apellidos            VARCHAR(100) NOT NULL,
  telefono             VARCHAR(30),
  email                VARCHAR(150),
  estado               VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO',
  idusuario            INTEGER      NOT NULL UNIQUE REFERENCES usuario (idusuario)
);

-- MEDICOESPECIALIDAD: resuelve MEDICO N — N ESPECIALIDAD
CREATE TABLE medicoespecialidad (
  idmedico        INTEGER NOT NULL REFERENCES medico (idmedico) ON DELETE CASCADE,
  idespecialidad  INTEGER NOT NULL REFERENCES especialidad (idespecialidad),
  PRIMARY KEY (idmedico, idespecialidad)
);

-- HORARIO N — 1 MEDICO (TIENE). DIASEMANA: 1 = lunes … 7 = domingo
CREATE TABLE horario (
  idhorario   INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  diasemana   INTEGER     NOT NULL CHECK (diasemana BETWEEN 1 AND 7),
  horainicio  TIME        NOT NULL,
  horafin     TIME        NOT NULL,
  estado      VARCHAR(20) NOT NULL DEFAULT 'ACTIVO',
  idmedico    INTEGER     NOT NULL REFERENCES medico (idmedico) ON DELETE CASCADE,
  CHECK (horafin > horainicio)
);

-- BLOQUEOAGENDA N — 1 MEDICO (TIENE). Sin horas = día completo
CREATE TABLE bloqueoagenda (
  idbloqueo   INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  fecha       DATE         NOT NULL,
  horainicio  TIME,
  horafin     TIME,
  tipo        VARCHAR(30)  NOT NULL,
  motivo      VARCHAR(255),
  idmedico    INTEGER      NOT NULL REFERENCES medico (idmedico) ON DELETE CASCADE,
  CHECK ((horainicio IS NULL AND horafin IS NULL) OR (horainicio IS NOT NULL AND horafin IS NOT NULL AND horafin > horainicio))
);

-- ---------------------------------------------------------------------
-- Pacientes
-- ---------------------------------------------------------------------
-- PACIENTE 0..1 — 1 USUARIO (PERTENECE)
CREATE TABLE paciente (
  idpaciente            INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  codigo                VARCHAR(20)  NOT NULL UNIQUE,
  ci                    VARCHAR(20)  NOT NULL UNIQUE,
  nombres               VARCHAR(100) NOT NULL,
  apellidos             VARCHAR(100) NOT NULL,
  fechanacimiento       DATE         NOT NULL,
  sexo                  VARCHAR(1)   NOT NULL CHECK (sexo IN ('M', 'F', 'O')),
  telefono              VARCHAR(30),
  email                 VARCHAR(150),
  direccion             VARCHAR(255),
  tiposangre            VARCHAR(5),
  aceptanotificaciones  BOOLEAN      NOT NULL DEFAULT TRUE,
  estado                VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO',
  idusuario             INTEGER      NOT NULL UNIQUE REFERENCES usuario (idusuario)
);

-- CONTACTOEMERGENCIA N — 1 PACIENTE (TIENE)
CREATE TABLE contactoemergencia (
  idcontacto  INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nombre      VARCHAR(150) NOT NULL,
  parentesco  VARCHAR(50)  NOT NULL,
  telefono    VARCHAR(30)  NOT NULL,
  idpaciente  INTEGER      NOT NULL REFERENCES paciente (idpaciente) ON DELETE CASCADE
);

-- HISTORIACLINICA 0..1 — 1 PACIENTE (TIENE)
CREATE TABLE historiaclinica (
  idhistoria               INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  numero                   VARCHAR(20) NOT NULL UNIQUE,
  antecedentespersonales   TEXT,
  antecedentesfamiliares   TEXT,
  alergias                 TEXT,
  enfermedadescronicas     TEXT,
  medicacionhabitual       TEXT,
  idpaciente               INTEGER     NOT NULL UNIQUE REFERENCES paciente (idpaciente) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- Citas
-- ---------------------------------------------------------------------
CREATE TABLE estadocita (
  codigo        VARCHAR(20)  PRIMARY KEY,
  nombre        VARCHAR(50)  NOT NULL,
  descripcion   VARCHAR(255),
  ocupahorario  BOOLEAN      NOT NULL DEFAULT TRUE
);

-- CITA N — 1 PACIENTE (RESERVA), N — 1 MEDICO (ATIENDE),
--      N — 1 ESPECIALIDAD (TIENE), N — 1 ESTADOCITA (CLASIFICA)
CREATE TABLE cita (
  idcita          INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  codigo          VARCHAR(30)  NOT NULL UNIQUE,
  fecha           DATE         NOT NULL,
  horainicio      TIME         NOT NULL,
  horafin         TIME         NOT NULL,
  modalidad       VARCHAR(20)  NOT NULL DEFAULT 'PRESENCIAL',
  motivo          VARCHAR(255),
  idpaciente      INTEGER      NOT NULL REFERENCES paciente (idpaciente),
  idmedico        INTEGER      NOT NULL REFERENCES medico (idmedico),
  idespecialidad  INTEGER      NOT NULL REFERENCES especialidad (idespecialidad),
  codigoestado    VARCHAR(20)  NOT NULL REFERENCES estadocita (codigo),
  CHECK (horafin > horainicio)
);
CREATE INDEX idx_cita_medico_fecha ON cita (idmedico, fecha);
CREATE INDEX idx_cita_paciente ON cita (idpaciente);

-- HISTORIALCITA N — 1 CITA (TIENE), N — 1 ESTADOCITA (REGISTRA)
CREATE TABLE historialcita (
  idhistorial    INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  accion         VARCHAR(30)  NOT NULL,
  fechaanterior  DATE,
  horaanterior   TIME,
  fechanueva     DATE,
  horanueva      TIME,
  motivo         VARCHAR(255),
  fecha          DATE         NOT NULL DEFAULT CURRENT_DATE,
  idcita         INTEGER      NOT NULL REFERENCES cita (idcita) ON DELETE CASCADE,
  codigoestado   VARCHAR(20)  NOT NULL REFERENCES estadocita (codigo)
);
CREATE INDEX idx_historialcita_cita ON historialcita (idcita);

-- ---------------------------------------------------------------------
-- Atención médica
-- ---------------------------------------------------------------------
-- CONSULTA 0..1 — 1 CITA (GENERA)
CREATE TABLE consulta (
  idconsulta          INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  fechahora           TIMESTAMPTZ      NOT NULL DEFAULT NOW(),
  motivo              VARCHAR(255),
  peso                DOUBLE PRECISION,
  talla               DOUBLE PRECISION,
  presionarterial     VARCHAR(20),
  temperatura         DOUBLE PRECISION,
  frecuenciacardiaca  INTEGER,
  examenfisico        TEXT,
  diagnostico         TEXT,
  observaciones       TEXT,
  idcita              INTEGER          NOT NULL UNIQUE REFERENCES cita (idcita)
);

CREATE TABLE medicamento (
  idmedicamento  INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nombre         VARCHAR(150) NOT NULL,
  presentacion   VARCHAR(100),
  estado         VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO'
);

-- TRATAMIENTO N — 1 CONSULTA (INDICA), N — 1 MEDICAMENTO (SE USA EN)
CREATE TABLE tratamiento (
  idtratamiento  INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  dosis          VARCHAR(100) NOT NULL,
  frecuencia     VARCHAR(100) NOT NULL,
  duracion       VARCHAR(100) NOT NULL,
  indicaciones   TEXT,
  idconsulta     INTEGER      NOT NULL REFERENCES consulta (idconsulta) ON DELETE CASCADE,
  idmedicamento  INTEGER      NOT NULL REFERENCES medicamento (idmedicamento)
);

CREATE TABLE catalogoestudio (
  idcatalogo   INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  nombre       VARCHAR(150) NOT NULL,
  categoria    VARCHAR(100),
  descripcion  VARCHAR(255),
  estado       VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO'
);

-- ESTUDIO N — 1 CONSULTA (SOLICITA), N — 1 CATALOGOESTUDIO (CLASIFICA)
CREATE TABLE estudio (
  idestudio         INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  indicaciones      TEXT,
  estado            VARCHAR(20)  NOT NULL DEFAULT 'SOLICITADO',
  fechasolicitud    TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
  fecharesultado    TIMESTAMPTZ,
  resultado         TEXT,
  archivoresultado  VARCHAR(500),
  idconsulta        INTEGER      NOT NULL REFERENCES consulta (idconsulta) ON DELETE CASCADE,
  idcatalogo        INTEGER      NOT NULL REFERENCES catalogoestudio (idcatalogo)
);

-- NOTIFICACION N — 0..1 CITA (GENERA), N — 0..1 ESTUDIO (GENERA)
CREATE TABLE notificacion (
  idnotificacion  INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  tipo            VARCHAR(30)  NOT NULL,
  canal           VARCHAR(20)  NOT NULL DEFAULT 'APP',
  mensaje         TEXT         NOT NULL,
  programadapara  TIMESTAMPTZ,
  enviadaen       TIMESTAMPTZ,
  leidaen         TIMESTAMPTZ,
  idcita          INTEGER      REFERENCES cita (idcita) ON DELETE CASCADE,
  idestudio       INTEGER      REFERENCES estudio (idestudio) ON DELETE CASCADE
);
CREATE INDEX idx_notificacion_cita ON notificacion (idcita);
CREATE INDEX idx_notificacion_estudio ON notificacion (idestudio);

-- ---------------------------------------------------------------------
-- Seguridad Supabase: el acceso es solo a través de la API (rol postgres).
-- Se activa RLS sin políticas para bloquear la API pública de Supabase.
-- ---------------------------------------------------------------------
ALTER TABLE rol                ENABLE ROW LEVEL SECURITY;
ALTER TABLE usuario            ENABLE ROW LEVEL SECURITY;
ALTER TABLE bitacora           ENABLE ROW LEVEL SECURITY;
ALTER TABLE configuracion      ENABLE ROW LEVEL SECURITY;
ALTER TABLE especialidad       ENABLE ROW LEVEL SECURITY;
ALTER TABLE medico             ENABLE ROW LEVEL SECURITY;
ALTER TABLE medicoespecialidad ENABLE ROW LEVEL SECURITY;
ALTER TABLE horario            ENABLE ROW LEVEL SECURITY;
ALTER TABLE bloqueoagenda      ENABLE ROW LEVEL SECURITY;
ALTER TABLE paciente           ENABLE ROW LEVEL SECURITY;
ALTER TABLE contactoemergencia ENABLE ROW LEVEL SECURITY;
ALTER TABLE historiaclinica    ENABLE ROW LEVEL SECURITY;
ALTER TABLE estadocita         ENABLE ROW LEVEL SECURITY;
ALTER TABLE cita               ENABLE ROW LEVEL SECURITY;
ALTER TABLE historialcita      ENABLE ROW LEVEL SECURITY;
ALTER TABLE consulta           ENABLE ROW LEVEL SECURITY;
ALTER TABLE medicamento        ENABLE ROW LEVEL SECURITY;
ALTER TABLE tratamiento        ENABLE ROW LEVEL SECURITY;
ALTER TABLE catalogoestudio    ENABLE ROW LEVEL SECURITY;
ALTER TABLE estudio            ENABLE ROW LEVEL SECURITY;
ALTER TABLE notificacion       ENABLE ROW LEVEL SECURITY;
