-- Migration: bookings table for MySQL
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,  
    date DATE NOT NULL,
    hour TINYINT UNSIGNED NOT NULL,
    unit VARCHAR(32) NOT NULL,
    status ENUM('active','cancelled') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    notified_at DATETIME DEFAULT NULL
);

-- Tabel users (minimal)
CREATE TABLE IF NOT EXISTS users (
    uuid CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    username VARCHAR(64) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(128) NOT NULL,
    unit VARCHAR(32) NOT NULL,
    whatsapp VARCHAR(32) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
