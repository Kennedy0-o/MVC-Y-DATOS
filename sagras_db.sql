-- =====================================================
-- BASE DE DATOS: sagras
-- Sistema de Gestion de Pedidos para Restobar
-- Modelo Entidad-Relacion adaptado a CodeIgniter 4
-- Guia 4: CRUD completo, Service, Reglas de Negocio
-- =====================================================
CREATE DATABASE IF NOT EXISTS sagras CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sagras;

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador','mozo','cocina','caja','gerente') NOT NULL DEFAULT 'mozo',
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mesa (
    id_mesa INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    capacidad INT NOT NULL DEFAULT 4,
    estado ENUM('libre','ocupada','reservada') NOT NULL DEFAULT 'libre',
    nivel ENUM('primer_nivel','segundo_nivel') DEFAULT 'primer_nivel',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS producto (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    id_categoria INT NOT NULL,
    disponible BOOLEAN NOT NULL DEFAULT TRUE,
    imagen VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria)
        REFERENCES categoria(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente','en_preparacion','listo','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_mesa FOREIGN KEY (id_mesa)
        REFERENCES mesa(id_mesa) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_pedido_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detalle_pedido (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (id_pedido)
        REFERENCES pedido(id_pedido) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto)
        REFERENCES producto(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pago (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL UNIQUE,
    metodo ENUM('efectivo','tarjeta','yape','plin','otro') NOT NULL DEFAULT 'efectivo',
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pago_pedido FOREIGN KEY (id_pedido)
        REFERENCES pedido(id_pedido) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS preparacion (
    id_preparacion INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL UNIQUE,
    estado ENUM('pendiente','en_progreso','completado','cancelado') NOT NULL DEFAULT 'pendiente',
    hora_inicio DATETIME DEFAULT NULL,
    hora_fin DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_preparacion_pedido FOREIGN KEY (id_pedido)
        REFERENCES pedido(id_pedido) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- DATOS DE PRUEBA
INSERT INTO categoria (nombre) VALUES ('Entradas'), ('Fondos'), ('Bebidas'), ('Postres');

INSERT INTO producto (nombre, precio, id_categoria, disponible) VALUES
('Salchipapa Clasica', 16.00, 1, TRUE),
('Salchipapa Saqra', 22.90, 1, TRUE),
('Cono Salchipapero', 22.00, 1, TRUE),
('Tequenos', 22.00, 1, TRUE),
('Alitas Mustard Honey', 20.00, 1, TRUE),
('Lomo Saltado', 35.00, 2, TRUE),
('Aji de Gallina', 25.00, 2, TRUE),
('Caldo de Cordero', 38.00, 2, TRUE),
('Hamburguesa', 27.00, 2, TRUE),
('Inca Kola', 6.00, 3, TRUE),
('Chicha Morada', 6.00, 3, TRUE);

INSERT INTO mesa (numero, capacidad, estado, nivel) VALUES
(1, 4, 'libre', 'primer_nivel'),
(2, 4, 'libre', 'primer_nivel'),
(3, 6, 'libre', 'primer_nivel'),
(4, 4, 'libre', 'primer_nivel'),
(5, 4, 'libre', 'primer_nivel'),
(6, 4, 'libre', 'primer_nivel'),
(10, 8, 'libre', 'segundo_nivel'),
(11, 8, 'libre', 'segundo_nivel'),
(12, 8, 'libre', 'segundo_nivel');

INSERT INTO usuario (nombre, email, password, rol, estado) VALUES
('Marco', 'mozo@gmail.com', '$2y$10$hashedpwd', 'mozo', 'activo'),
('Chef Ana', 'cocina@gmail.com', '$2y$10$hashedpwd', 'cocina', 'activo'),
('Gerente Luis', 'gerente@gmail.com', '$2y$10$hashedpwd', 'gerente', 'activo');
