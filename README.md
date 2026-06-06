# 🎮 Store Top Up Game – Brainstorming Proyek UAS  
**Platform:** PHP Native + MySQL | **Tema:** Store Top Up Game (diamond, voucher, coin)

---

## 1. Nama Proyek & Domain (Contoh)
- `TopUpin`
- `GameBoost Store`
- `VoucherKu`

### Fitur Utama
- User registrasi/login (sederhana, tanpa framework)
- Menampilkan daftar produk top up (diamond, voucher, coin)
- Keranjang / transaksi langsung
- Proses pembayaran (simulasi, misal pilih metode: transfer, e-wallet)
- Riwayat transaksi user
- Admin panel (kelola produk, lihat transaksi)

---

## 2. Rancangan Tabel (MySQL) – DDL & Relasi

```sql
CREATE DATABASE topup_store;
USE topup_store;

-- Tabel user (pelanggan)
CREATE TABLE users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- simpan hash
    email VARCHAR(100),
    no_hp VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel game (opsional, jika ingin filter per game)
CREATE TABLE games (
    id_game INT PRIMARY KEY AUTO_INCREMENT,
    nama_game VARCHAR(100) NOT NULL,
    publisher VARCHAR(100)
);

-- Tabel produk top up
CREATE TABLE products (
    id_produk INT PRIMARY KEY AUTO_INCREMENT,
    id_game INT NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    nominal VARCHAR(20), -- misal "100 Diamond", "500 UC"
    harga INT NOT NULL,
    stok INT DEFAULT 999, -- untuk stok virtual
    FOREIGN KEY (id_game) REFERENCES games(id_game) ON DELETE CASCADE
);

-- Tabel transaksi
CREATE TABLE transactions (
    id_transaksi INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    tgl_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_harga INT NOT NULL,
    status ENUM('pending', 'sukses', 'gagal') DEFAULT 'pending',
    metode_pembayaran VARCHAR(50),
    FOREIGN KEY (id_user) REFERENCES users(id_user)
);

-- Tabel detail transaksi (banyak barang dalam 1 transaksi)
CREATE TABLE detail_transaksi (
    id_detail INT PRIMARY KEY AUTO_INCREMENT,
    id_transaksi INT NOT NULL,
    id_produk INT NOT NULL,
    qty INT NOT NULL,
    subtotal INT NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transactions(id_transaksi) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES products(id_produk)
);

-- Contoh data awal (insert)
INSERT INTO games (nama_game, publisher) VALUES 
('Mobile Legends', 'Moonton'),
('Free Fire', 'Garena'),
('PUBG Mobile', 'Tencent');

INSERT INTO products (id_game, nama_produk, nominal, harga) VALUES
(1, 'Weekly Diamond Pass', '100 Diamond', 15000),
(1, 'Twilight Pass', '250 Diamond', 35000),
(2, 'Membership', '1 Bulan', 50000);
```

---

## 3. Implementasi JOIN (Modul 6) – Wajib

### INNER JOIN (3 tabel: transaksi + detail + produk)

```sql
SELECT t.id_transaksi, t.tgl_transaksi, p.nama_produk, dt.qty, dt.subtotal
FROM transactions t
INNER JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
INNER JOIN products p ON dt.id_produk = p.id_produk
WHERE t.id_user = 1
ORDER BY t.tgl_transaksi DESC;
```

### LEFT JOIN (semua produk + nama game)

```sql
SELECT p.nama_produk, p.harga, g.nama_game
FROM products p
LEFT JOIN games g ON p.id_game = g.id_game;
```

---

## 4. Fungsi Agregat & GROUP BY (Modul 5)

### Dashboard Admin: total pendapatan, produk terlaris, jumlah transaksi per metode

```sql
-- Total pendapatan dari transaksi sukses
SELECT SUM(total_harga) AS pendapatan FROM transactions WHERE status = 'sukses';

-- Produk terlaris (berdasarkan qty)
SELECT p.nama_produk, SUM(dt.qty) AS total_terjual
FROM detail_transaksi dt
INNER JOIN products p ON dt.id_produk = p.id_produk
INNER JOIN transactions t ON dt.id_transaksi = t.id_transaksi
WHERE t.status = 'sukses'
GROUP BY p.id_produk
ORDER BY total_terjual DESC
LIMIT 5;

-- Jumlah transaksi per metode pembayaran
SELECT metode_pembayaran, COUNT(*) AS jumlah
FROM transactions
WHERE status = 'sukses'
GROUP BY metode_pembayaran;
```

### Contoh HAVING (produk dengan pendapatan > 100.000)

```sql
SELECT p.nama_produk, SUM(dt.subtotal) as pendapatan
FROM detail_transaksi dt
JOIN products p ON dt.id_produk = p.id_produk
JOIN transactions t ON dt.id_transaksi = t.id_transaksi
WHERE t.status = 'sukses'
GROUP BY p.id_produk
HAVING pendapatan > 100000;
```

---

## 5. Fungsi String (Modul 4)

```sql
-- Username dalam huruf besar
SELECT UPPER(username) AS username_upper, email FROM users;

-- Mencari produk mengandung "diamond" (case-insensitive)
SELECT * FROM products WHERE LOWER(nama_produk) LIKE LOWER('%diamond%');

-- 3 karakter pertama dari nama produk
SELECT id_produk, LEFT(nama_produk, 3) AS kode_singkat, harga FROM products;

-- Generate kode voucher (contoh)
SELECT CONCAT(UPPER(LEFT(g.nama_game,3)), t.id_transaksi, RIGHT(t.tgl_transaksi,2)) AS kode_voucher
FROM transactions t
JOIN users u ON t.id_user = u.id_user
JOIN games g ON ...;
```

---

## 6. Arsitektur PHP Native (Tanpa Framework)

### Struktur Folder

```
topup_store/
│
├── config/
│   └── database.php   (koneksi MySQL menggunakan mysqli atau PDO)
├── assets/
│   ├── css/
│   └── js/
├── pages/
│   ├── index.php      (daftar produk)
│   ├── cart.php
│   ├── checkout.php
│   ├── history.php
│   ├── login.php
│   └── register.php
├── admin/
│   ├── dashboard.php
│   ├── products.php
│   └── transactions.php
├── process/
│   ├── add_to_cart.php
│   ├── process_order.php
│   ├── login_proc.php
│   └── register_proc.php
└── includes/
    ├── header.php
    └── footer.php
```

### Contoh `config/database.php` (MySQLi)

```php
<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'topup_store';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
```

### Contoh Halaman Produk (dengan JOIN & ORDER BY)

```php
<?php
include 'config/database.php';
$sql = "SELECT p.*, g.nama_game 
        FROM products p 
        LEFT JOIN games g ON p.id_game = g.id_game 
        ORDER BY p.harga ASC";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    echo "<div>".$row['nama_produk']." - Rp ".number_format($row['harga'])." (".$row['nama_game'].")</div>";
}
?>
```

---

## 7. Keterkaitan dengan Materi Praktikum (Modul 2–6)

| Modul | Materi | Implementasi di Proyek Top Up Game |
|-------|--------|--------------------------------------|
| **2 (DDL)** | CREATE, ALTER, DROP | Script pembuatan database & tabel. Jika perlu alter (misal nambah kolom diskon), bisa dilakukan. |
| **3 (DML)** | INSERT, SELECT, UPDATE, DELETE | Admin tambah produk (INSERT), user update profil, admin hapus produk, update status transaksi. |
| **4 (String)** | UPPER, SUBSTRING, LENGTH, dll. | Tampilkan username uppercase, filter produk dengan LOWER, generate kode transaksi. |
| **5 (Aggregate)** | AVG, SUM, COUNT, MAX, GROUP BY | Dashboard: total pendapatan, produk terlaris, rata-rata transaksi per user. |
| **6 (JOIN)** | INNER, LEFT, RIGHT | Laporan transaksi (join 3 tabel), daftar produk dengan nama game, user yang belum pernah transaksi (LEFT JOIN + HAVING). |

---

## 8. Tips Presentasi (9 Juni 2026)

- **Demo langsung** aplikasi native PHP di localhost (XAMPP/Laragon).  
- Tunjukkan **kode SQL yang kompleks** (JOIN 3 tabel, GROUP BY dengan HAVING) di file PHP, jelaskan baris per baris.  
- Buka phpMyAdmin dan tunjukkan **struktur tabel** (foreign key) serta **trigger/view** jika Anda tambahkan (nilai plus).  
- **Skenario demo:**  
  1. User registrasi/login.  
  2. Pilih produk top up -> tambah ke keranjang.  
  3. Checkout, pilih metode pembayaran.  
  4. Admin login -> ubah status transaksi menjadi sukses.  
  5. Tampilkan laporan penjualan (SUM, GROUP BY).

---

## 9. Next Step (Pengembangan Lebih Lanjut)

- **Full script SQL** (CREATE, INSERT sample data, VIEW untuk laporan)  
- **Kode PHP native lengkap** untuk setiap halaman (login, cart, checkout, admin dashboard)  
- **Implementasi fungsi agregat dan JOIN** dengan MySQLi secara konkret  
- **Trigger** untuk update stok otomatis setelah transaksi sukses (nilai tambah)

---

**Dibuat untuk UAS – Praktikum Basis Data & Pemrograman Web**  
*Silakan kembangkan sesuai kebutuhan dan presentasikan dengan percaya diri!*
