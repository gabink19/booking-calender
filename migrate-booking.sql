-- Migration: bookings table for MySQL
CREATE TABLE IF NOT EXISTS bookings (
    date DATE NOT NULL,
    hour TINYINT UNSIGNED NOT NULL,
    unit VARCHAR(32) NOT NULL,
    name VARCHAR(128) NOT NULL,
    whatsapp VARCHAR(32) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (date, hour, unit)
);
-- Example:
-- INSERT INTO bookings (date, hour, unit, name, whatsapp) VALUES ('2025-07-23', 10, 'A1', 'Budi', '08123456789');
