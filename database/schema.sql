-- --------------------------------------------------------
-- Database: topup_store
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS topup_store;
USE topup_store;

-- 1. Tabel users
CREATE TABLE IF NOT EXISTS users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    no_hp VARCHAR(15),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel games
CREATE TABLE IF NOT EXISTS games (
    id_game INT PRIMARY KEY AUTO_INCREMENT,
    nama_game VARCHAR(100) NOT NULL,
    publisher VARCHAR(100),
    logo VARCHAR(255) DEFAULT 'default_game.png'
) ENGINE=InnoDB;

-- 3. Tabel products
CREATE TABLE IF NOT EXISTS products (
    id_produk INT PRIMARY KEY AUTO_INCREMENT,
    id_game INT NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    nominal VARCHAR(50) NOT NULL,
    harga INT NOT NULL,
    stok INT DEFAULT 999,
    FOREIGN KEY (id_game) REFERENCES games(id_game) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tabel transactions
CREATE TABLE IF NOT EXISTS transactions (
    id_transaksi INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    game_account_id VARCHAR(50) NOT NULL,
    game_zone_id VARCHAR(50),
    tgl_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_harga INT NOT NULL,
    status ENUM('pending', 'sukses', 'gagal') DEFAULT 'pending',
    metode_pembayaran VARCHAR(50),
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabel detail_transaksi
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_transaksi INT NOT NULL,
    id_produk INT NOT NULL,
    qty INT NOT NULL,
    subtotal INT NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transactions(id_transaksi) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES products(id_produk) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- TRIGGER: Kurangi stok produk secara otomatis setelah detail_transaksi ditambahkan
-- --------------------------------------------------------
DROP TRIGGER IF EXISTS reduce_stock_on_detail_insert;
DELIMITER //
CREATE TRIGGER reduce_stock_on_detail_insert
AFTER INSERT ON detail_transaksi
FOR EACH ROW
BEGIN
    UPDATE products 
    SET stok = stok - NEW.qty
    WHERE id_produk = NEW.id_produk;
END//
DELIMITER ;

-- --------------------------------------------------------
-- VIEW: Laporan Detail Transaksi Lengkap (JOIN 4 Tabel)
-- --------------------------------------------------------
CREATE OR REPLACE VIEW view_laporan_transaksi AS
SELECT 
    t.id_transaksi,
    t.tgl_transaksi,
    t.game_account_id,
    t.game_zone_id,
    u.username,
    u.email,
    g.nama_game,
    p.nama_produk,
    p.nominal,
    p.harga AS harga_satuan,
    dt.qty,
    dt.subtotal,
    t.metode_pembayaran,
    t.status
FROM transactions t
INNER JOIN users u ON t.id_user = u.id_user
INNER JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
INNER JOIN products p ON dt.id_produk = p.id_produk
INNER JOIN games g ON p.id_game = g.id_game;

-- --------------------------------------------------------
-- INSERT DATA SAMPEL
-- --------------------------------------------------------

-- Reset data
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE detail_transaksi;
TRUNCATE TABLE transactions;
TRUNCATE TABLE products;
TRUNCATE TABLE games;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Users (admin123 & user123)
INSERT INTO users (id_user, username, password, email, no_hp, role) VALUES
(1, 'admin', '$2y$10$Oo4R020XYYuJ7kaDxe2pxeKCGXfdHew8zcoIfWHJc82HIRdZMuMgG', 'admin@topupin.com', '081234567890', 'admin'),
(2, 'user', '$2y$10$jhY/awzsjXRSBOAtBoj4SeciinPRNfdqLGkEXwh9YagSegba5KtQm', 'user@gmail.com', '089876543210', 'user'),
(3, 'alvin', '$2y$10$0slzHN4awUceqocILmQwle4TjuxPbK8DEMaiVvY0SvzfYsqqUbWPu', 'alvin@storegame.com', '08111222333', 'user');

-- Games
INSERT INTO games (id_game, nama_game, publisher, logo) VALUES
(1, 'Mobile Legends', 'Moonton', 'mlbb.png'),
(2, 'Free Fire', 'Garena', 'ff.png'),
(3, 'PUBG Mobile', 'Tencent', 'pubg.png'),
(4, 'Genshin Impact', 'Hoyoverse', 'genshin.png');

-- Products
INSERT INTO products (id_game, nama_produk, nominal, harga, stok) VALUES
-- Mobile Legends
(1, '86 Diamonds', '86 Diamonds', 20000, 500),
(1, '172 Diamonds', '172 Diamonds', 40000, 500),
(1, '257 Diamonds', '257 Diamonds', 60000, 500),
(1, '706 Diamonds', '706 Diamonds', 155000, 200),
(1, 'Weekly Diamond Pass', '1 Pass', 28000, 1000),
-- Free Fire
(2, '50 Diamonds', '50 Diamonds', 8000, 600),
(2, '140 Diamonds', '140 Diamonds', 20000, 600),
(2, '355 Diamonds', '355 Diamonds', 48000, 400),
(2, '720 Diamonds', '720 Diamonds', 95000, 300),
-- PUBG Mobile
(3, '60 UC', '60 UC', 15000, 800),
(3, '325 UC', '325 UC', 70000, 400),
(3, '660 UC', '660 UC', 140000, 300),
-- Genshin Impact
(4, 'Genesis Crystal 60', '60 Crystals', 16000, 999),
(4, 'Genesis Crystal 300', '300 Crystals', 79000, 999),
(4, 'Welkin Moon', 'Blessing Welkin', 79000, 500);

-- Transactions & Details (Untuk data grafik/Dashboard Laporan awal)
-- Transaksi 1: Sukses, User: user (2)
INSERT INTO transactions (id_transaksi, id_user, game_account_id, game_zone_id, tgl_transaksi, total_harga, status, metode_pembayaran) VALUES
(1, 2, '12345678', '1234', '2026-06-01 10:00:00', 40000, 'sukses', 'QRIS');
INSERT INTO detail_transaksi (id_transaksi, id_produk, qty, subtotal) VALUES
(1, 2, 1, 40000); -- 172 Diamonds MLBB

-- Transaksi 2: Sukses, User: alvin (3)
INSERT INTO transactions (id_transaksi, id_user, game_account_id, game_zone_id, tgl_transaksi, total_harga, status, metode_pembayaran) VALUES
(2, 3, '87654321', NULL, '2026-06-02 14:30:00', 48000, 'sukses', 'GoPay');
INSERT INTO detail_transaksi (id_transaksi, id_produk, qty, subtotal) VALUES
(2, 8, 1, 48000); -- 355 Diamonds FF

-- Transaksi 3: Pending, User: alvin (3)
INSERT INTO transactions (id_transaksi, id_user, game_account_id, game_zone_id, tgl_transaksi, total_harga, status, metode_pembayaran) VALUES
(3, 3, '55443322', '8888', '2026-06-05 20:15:00', 28000, 'pending', 'Transfer Bank');
INSERT INTO detail_transaksi (id_transaksi, id_produk, qty, subtotal) VALUES
(3, 5, 1, 28000); -- Weekly Pass MLBB

-- Transaksi 4: Gagal, User: user (2)
INSERT INTO transactions (id_transaksi, id_user, game_account_id, game_zone_id, tgl_transaksi, total_harga, status, metode_pembayaran) VALUES
(4, 2, '777666', NULL, '2026-06-03 09:00:00', 70000, 'gagal', 'ShopeePay');
INSERT INTO detail_transaksi (id_transaksi, id_produk, qty, subtotal) VALUES
(4, 11, 1, 70000); -- 325 UC PUBG
