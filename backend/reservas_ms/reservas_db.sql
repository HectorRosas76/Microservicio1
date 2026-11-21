-- Base de datos de reservas_ms
-- OJO: cliente_id y vehiculo_id YA NO son FOREIGN KEY, porque clientes y
-- vehiculos viven en otras bases de datos (otros microservicios).
-- La existencia del cliente/vehículo la valida reservas_ms por HTTP.
CREATE DATABASE IF NOT EXISTS reservas_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE reservas_db;

CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('activa', 'completada', 'cancelada') DEFAULT 'activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reservas_cliente (cliente_id),
    INDEX idx_reservas_vehiculo (vehiculo_id, estado, fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
