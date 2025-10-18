-- Crear base y tablas
CREATE DATABASE IF NOT EXISTS biblioteca_municipal
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE biblioteca_municipal;

-- Tabla usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL,
  dni VARCHAR(20) NOT NULL,
  telefono VARCHAR(30),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_correo (correo),
  UNIQUE KEY uq_usuarios_dni (dni)
) ENGINE=InnoDB;

-- Tabla prestamos
CREATE TABLE IF NOT EXISTS prestamos(
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuarios_id INT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  nombre_libro VARCHAR(255) NOT NULL,
  estado_prestamo ENUM('Prestado','De vuelto','En Biblioteca') DEFAULT 'Prestado',
  imagen_libro VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_prestamos_usuarios
    FOREIGN KEY (usuarios_id) REFERENCES usuarios(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  KEY idx_prestamos_fecha_inicio (fecha_inicio),
  KEY idx_prestamos_fecha_fin (fecha_fin),
  KEY idx_prestamos_usuario (usuarios_id)
) ENGINE=InnoDB;

select * from usuarios;
select * from prestamos;


