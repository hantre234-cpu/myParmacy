-- ============================================================
--  Pharmacy Management System - Database Setup
--  Compatible with MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS pharmacy_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pharmacy_db;

-- ─────────────────────────────────────────
-- Table: sellers
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sellers (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(80)  NOT NULL,
    last_name   VARCHAR(80)  NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    phone       VARCHAR(20)  NOT NULL,
    address     TEXT,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- Table: medicines
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS medicines (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    description  TEXT,
    category     VARCHAR(80),
    price        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock        INT UNSIGNED  NOT NULL DEFAULT 0,
    unit         VARCHAR(20)   DEFAULT 'pcs',
    expiry_date  DATE,
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- Table: cash_registers
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cash_registers (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id        INT UNSIGNED NOT NULL,
    label            VARCHAR(100) DEFAULT 'Caisse',
    opening_time     DATETIME,
    closing_time     DATETIME,
    opening_amount   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    closing_amount   DECIMAL(12,2) DEFAULT NULL,
    status           ENUM('open','closed') DEFAULT 'open',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- Table: sales
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sales (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cash_register_id  INT UNSIGNED NOT NULL,
    medicine_id       INT UNSIGNED NOT NULL,
    quantity          INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price        DECIMAL(10,2) NOT NULL,
    total_price       DECIMAL(12,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    sale_date         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id)      REFERENCES medicines(id)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────
-- Seed data (demo)
-- ─────────────────────────────────────────
INSERT INTO sellers (first_name, last_name, email, phone, address) VALUES
('Ahmed',   'Benali',   'a.benali@pharma.dz',  '0551234567', 'Alger Centre'),
('Sara',    'Meziane',  's.meziane@pharma.dz', '0661234567', 'Oran'),
('Karim',   'Hadj',     'k.hadj@pharma.dz',    '0771234567', 'Constantine');

INSERT INTO medicines (name, description, category, price, stock, unit, expiry_date) VALUES
('Doliprane 1000mg', 'Paracétamol analgésique', 'Analgésique',    150.00, 200, 'boîte', '2027-06-30'),
('Amoxicilline 500mg','Antibiotique aminopénicilline','Antibiotique', 320.00,  80, 'boîte', '2026-12-31'),
('Ibuprofène 400mg', 'Anti-inflammatoire AINS',  'AINS',           180.00, 150, 'boîte', '2027-03-15'),
('Metformine 850mg', 'Antidiabétique oral',       'Diabétologie',  210.00,  60, 'boîte', '2026-09-01'),
('Oméprazole 20mg',  'Inhibiteur pompe à protons','Gastro',        260.00, 120, 'boîte', '2027-01-20');

INSERT INTO cash_registers (seller_id, label, opening_time, opening_amount, status) VALUES
(1, 'Caisse Principale', NOW(), 5000.00, 'open');

INSERT INTO sales (cash_register_id, medicine_id, quantity, unit_price) VALUES
(1, 1, 3, 150.00),
(1, 3, 2, 180.00),
(1, 5, 1, 260.00);
