CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS api_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    revoked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_api_tokens_user
    FOREIGN KEY (user_id)
    REFERENCES api_users(id)
    ON DELETE CASCADE
);

-- Usuario de prueba: username "admin", password "MiClaveSegura123"
INSERT INTO api_users (username, email, password_hash) VALUES
('admin', 'admin@example.com', '$2y$10$eIsrYZBZPHHYBfRFh.JY0u6IpgRtOQp14CLsgSdWEMQEc0P0B2Qey');

-- 1. Insertar 10 registros en la tabla 'users'
INSERT INTO users (name, email) VALUES
('Juan Pérez', 'juan.perez@example.com'),
('María García', 'maria.garcia@example.com'),
('Carlos López', 'carlos.lopez@example.com'),
('Ana Martínez', 'ana.martinez@example.com'),
('Luis Rodríguez', 'luis.rodriguez@example.com'),
('Laura Hernández', 'laura.hernandez@example.com'),
('Diego Gómez', 'diego.gomez@example.com'),
('Sofia Díaz', 'sofia.diaz@example.com'),
('Fernando Torres', 'fernando.torres@example.com'),
('Patricia Morales', 'patricia.morales@example.com');

-- 2. Insertar 10 registros en la tabla 'productos'
INSERT INTO productos (sku, name, description, price, stock) VALUES
('PROD-001', 'Laptop Pro 15', 'Computadora portátil de alto rendimiento con 16GB RAM y 512GB SSD', 1299.99, 25),
('PROD-002', 'Smartphone X', 'Teléfono inteligente con pantalla OLED de 6.5 pulgadas y cámara triple', 799.50, 50),
('PROD-003', 'Audífonos Bluetooth', 'Audífonos inalámbricos con cancelación activa de ruido', 149.00, 100),
('PROD-004', 'Monitor Gamer 27"', 'Monitor QHD de 165Hz con tiempo de respuesta de 1ms', 320.00, 15),
('PROD-005', 'Teclado Mecánico RGB', 'Teclado mecánico con interruptores Red y retroiluminación personalizable', 89.99, 40),
('PROD-006', 'Mouse Inalámbrico', 'Mouse ergonómico con sensor óptico de alta precisión', 35.50, 80),
('PROD-007', 'Silla Ergonómica', 'Silla de oficina con soporte lumbar ajustable y reposabrazos 3D', 210.00, 12),
('PROD-008', 'Disco Duro Externo 2TB', 'Unidad de almacenamiento portátil USB 3.0', 75.25, 60),
('PROD-009', 'Cámara Web Full HD', 'Webcam 1080p con micrófono estéreo integrado para videollamadas', 55.00, 30),
('PROD-010', 'Escritorio Elevable', 'Escritorio con ajuste de altura eléctrico y memoria de posiciones', 450.00, 8);

