CREATE DATABASE IF NOT EXISTS inventaris_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE inventaris_db;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category_id INT NOT NULL,
    supplier_id INT NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_products_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS stock_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(150) NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (id, name) VALUES
(1, 'Aksesoris Komputer'),
(2, 'Display & Monitor'),
(3, 'Perangkat Jaringan'),
(4, 'Storage & Memori'),
(5, 'Komponen Utama');

INSERT IGNORE INTO suppliers (id, name, phone) VALUES
(1, 'PT Nusantara Tech Supplier', '081234567890'),
(2, 'CV Aksesoris Medan Jaya', '082198765432'),
(3, 'Distributor Digital Indonesia', '081370001122'),
(4, 'PT Global Hardware Utama', '085261112233'),
(5, 'CV Megah Elektrikal', '081900112233');

INSERT IGNORE INTO products (id, name, category_id, supplier_id, price, stock) VALUES
(1, 'Logitech Wireless Mouse M170', 1, 2, 165000.00, 25),
(2, 'Monitor LED LG 24 Inch Full HD', 2, 1, 1450000.00, 10),
(3, 'SSD NVMe Samsung 980 500GB', 4, 3, 850000.00, 15),
(4, 'Router TP-Link Archer C6 AC1200', 3, 4, 395000.00, 8),
(5, 'Keyboard Mechanical Keychron K2', 1, 5, 1250000.00, 12);

INSERT IGNORE INTO stock_logs (id, product_name, action) VALUES
(1, 'Mouse Lama', 'DELETE_PRODUCT'),
(2, 'Keyboard Bekas', 'DELETE_PRODUCT'),
(3, 'Monitor Rusak', 'DELETE_PRODUCT'),
(4, 'Kabel LAN 5m', 'DELETE_PRODUCT'),
(5, 'Flashdisk 8GB', 'DELETE_PRODUCT');