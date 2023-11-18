-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS banco3;

-- Usar la base de datos
USE banco3;

-- Crear la tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL,
    contrasena VARCHAR(255) NOT NULL
);

-- Insertar usuarios de ejemplo
INSERT INTO usuarios (nombre_usuario, contrasena) VALUES
    ('usuario1', 'contrasena1'),
    ('usuario2', 'contrasena2');

-- Crear la tabla de cuentas asociadas a usuarios
CREATE TABLE IF NOT EXISTS cuentas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_cuenta VARCHAR(20) NOT NULL,
    saldo DECIMAL(10, 2) NOT NULL,
    usuario_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Insertar cuentas de ejemplo asociadas a un usuario existente con id=1
INSERT INTO cuentas (numero_cuenta, saldo, usuario_id) VALUES
    ('123456789', 1000.00, 1),
    ('987654321', 500.00, 2);
