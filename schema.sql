-- 📦 MODELO ENTIDAD-RELACIÓN (SQL) — SISTEMA DE TICKETS ENTÉRICO
-- Base de datos para el sistema de tickets, centrada en roles, tickets, auditoría y configuración.


CREATE DATABASE ticketsdb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ticketsdb;


-- 1. Tabla de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE -- 'cliente', 'tecnico', 'admin'
);

-- 2. Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- 3. Tabla de tickets
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    prioridad ENUM('baja','media','alta','urgente') NOT NULL,
    estado ENUM('abierto','en_proceso','en_espera','cerrado') DEFAULT 'abierto',
    cliente_id INT NOT NULL,
    tecnico_id INT DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cerrado_automaticamente BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id)
);

-- 4. Tabla de comentarios
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    usuario_id INT NOT NULL,
    contenido TEXT NOT NULL,
    interno BOOLEAN DEFAULT FALSE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- 5. Tabla de auditoría
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(255) NOT NULL,
    ip VARCHAR(45),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- 6. Configuración general del sistema
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_empresa VARCHAR(255),
    zona_horaria VARCHAR(100),
    tiempo_max_respuesta INT, -- en minutos
    tiempo_cierre_tras_respuesta INT, -- en minutos
    smtp_host VARCHAR(255),
    smtp_user VARCHAR(255),
    smtp_pass VARCHAR(255),
    notificaciones_email BOOLEAN DEFAULT FALSE
);

-- 7. SLA
CREATE TABLE sla (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prioridad ENUM('baja','media','alta','urgente') NOT NULL UNIQUE,
    tiempo_respuesta INT NOT NULL, -- en minutos
    tiempo_resolucion INT NOT NULL -- en minutos
);

-- 8. Reportes dinámicos generados
CREATE TABLE reportes_dinamicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    campos TEXT,
    rango_inicio DATE,
    rango_fin DATE,
    generado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
