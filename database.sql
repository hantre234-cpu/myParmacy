-- ============================================================
--  PharmaCare - Database Schema
--  Université UHBC | FSEI | DAW - L2 INF G03
-- ============================================================

CREATE DATABASE IF NOT EXISTS pharmacare
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pharmacare;

-- ─────────────────────────────────────────────
--  Table: sellers
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sellers (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(100)    NOT NULL,
    last_name   VARCHAR(100)    NOT NULL,
    email       VARCHAR(150)    NOT NULL UNIQUE,
    phone       VARCHAR(20),
    password    VARCHAR(255)    NOT NULL,       -- bcrypt hashed
    role        ENUM('admin','seller') DEFAULT 'seller',
    is_active   TINYINT(1)      DEFAULT 1,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  Table: categories  (drug categories)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  Table: medicines
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS medicines (
    id            INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150)    NOT NULL,
    generic_name  VARCHAR(150),
    category_id   INT UNSIGNED,
    dosage        VARCHAR(100),              -- e.g. "500mg"
    price         DECIMAL(10,2)  NOT NULL,
    stock         INT UNSIGNED   DEFAULT 0,
    expiry_date   DATE,
    description   TEXT,
    requires_rx   TINYINT(1)     DEFAULT 0,  -- requires prescription
    created_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  Table: cash_registers
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cash_registers (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    label           VARCHAR(100)    NOT NULL,         -- e.g. "Caisse N°1"
    seller_id       INT UNSIGNED,
    opening_time    DATETIME,
    closing_time    DATETIME,
    amount_opening  DECIMAL(10,2)   DEFAULT 0.00,
    amount_closing  DECIMAL(10,2)   DEFAULT 0.00,
    status          ENUM('open','closed') DEFAULT 'closed',
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  Table: sales
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sales (
    id               INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    cash_register_id INT UNSIGNED    NOT NULL,
    seller_id        INT UNSIGNED,
    total_amount     DECIMAL(10,2)   NOT NULL,
    payment_method   ENUM('cash','card','other') DEFAULT 'cash',
    note             TEXT,
    sold_at          TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cash_register_id) REFERENCES cash_registers(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id)        REFERENCES sellers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  Table: sale_items  (detail lines of a sale)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sale_items (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    sale_id     INT UNSIGNED    NOT NULL,
    medicine_id INT UNSIGNED,
    quantity    INT UNSIGNED    NOT NULL DEFAULT 1,
    unit_price  DECIMAL(10,2)  NOT NULL,
    subtotal    DECIMAL(10,2)  GENERATED ALWAYS AS (quantity * unit_price) STORED,
    FOREIGN KEY (sale_id)     REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────
--  Seed data
-- ─────────────────────────────────────────────
INSERT INTO categories (name) VALUES
    ('Antibiotiques'),
    ('Analgésiques'),
    ('Anti-inflammatoires'),
    ('Antihistaminiques'),
    ('Vitamines & Compléments'),
    ('Dermatologie'),
    ('Cardiologie'),
    ('Diabétologie');

-- Default admin  (password: Admin@1234  — bcrypt)
INSERT INTO sellers (first_name, last_name, email, phone, password, role) VALUES
    ('Admin', 'PharmaCare', 'admin@pharmacare.dz', '0555000000',
     '$2y$12$eImiTXuWVxfM37uY4JANjQ==Eq0y5Gg4pGPf5FoWUHxFvX.xbkPy2', 'admin');

INSERT INTO medicines (name, generic_name, category_id, dosage, price, stock, expiry_date, requires_rx) VALUES
    ('Amoxicilline 500mg', 'Amoxicilline', 1, '500mg', 350.00, 120, '2026-12-31', 1),
    ('Paracétamol 1g',     'Paracétamol',  2, '1000mg',  80.00, 500, '2027-06-30', 0),
    ('Ibuprofène 400mg',   'Ibuprofène',   3, '400mg',  120.00, 200, '2026-09-15', 0),
    ('Loratadine 10mg',    'Loratadine',   4, '10mg',   150.00,  80, '2027-03-20', 0),
    ('Vitamine C 1000mg',  'Acide ascorbique', 5, '1000mg', 200.00, 300, '2027-01-10', 0);
