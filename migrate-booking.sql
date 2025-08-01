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

ALTER TABLE users
ADD COLUMN IF NOT EXISTS is_admin BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE users
ADD COLUMN IF NOT EXISTS is_active BOOLEAN NOT NULL DEFAULT TRUE;

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(64) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
);

INSERT INTO settings (key_name, value) VALUES
    ('app_name', 'Booking App'),
    ('app_logo', 'logo.jpg'),
    ('app_background', 'background.png'),
    ('contact', '0812-1234-5678');