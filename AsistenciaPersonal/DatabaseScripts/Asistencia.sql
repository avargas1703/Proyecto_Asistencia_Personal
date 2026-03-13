CREATE DATABASE IF NOT EXISTS asistencia_personal;
USE asistencia_personal;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  firebase_uid VARCHAR(128) NOT NULL UNIQUE,
  correo VARCHAR(150) NOT NULL,
  nombre VARCHAR(100) NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS asistencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  fecha DATE NOT NULL,
  hora_entrada TIME NULL,
  hora_salida TIME NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_asistencias_usuarios
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE
);

-- Solo 1 asistencia por usuario por día
CREATE UNIQUE INDEX ux_asistencia_usuario_fecha 
ON asistencias(usuario_id, fecha);