<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once '../config/database.php';

// Check auth & admin role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['error'] = 'Akses ditolak! Anda bukan admin.';
    header('Location: ../index.php');
    exit;
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add Game
    if ($action === 'add_game') {
        $nama_game = trim($_POST['nama_game'] ?? '');
        $publisher = trim($_POST['publisher'] ?? '');
        $logo = trim($_POST['logo'] ?? 'default_game.png');

        if (empty($nama_game) || empty($publisher)) {
            $_SESSION['error'] = 'Nama Game dan Publisher wajib diisi!';
            header('Location: ../pages/admin/products.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO games (nama_game, publisher, logo) VALUES (?, ?, ?)");
            $stmt->execute([$nama_game, $publisher, $logo]);
            $_SESSION['success'] = 'Game berhasil ditambahkan!';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Gagal menambah game: ' . $e->getMessage();
        }
        header('Location: ../pages/admin/products.php');
        exit;
    }

    // Add Product
    if ($action === 'add_product') {
        $id_game = intval($_POST['id_game'] ?? 0);
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $nominal = trim($_POST['nominal'] ?? '');
        $harga = intval($_POST['harga'] ?? 0);
        $stok = intval($_POST['stok'] ?? 999);

        if ($id_game <= 0 || empty($nama_produk) || empty($nominal) || $harga <= 0) {
            $_SESSION['error'] = 'Data produk tidak lengkap atau harga tidak valid!';
            header('Location: ../pages/admin/products.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO products (id_game, nama_produk, nominal, harga, stok) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_game, $nama_produk, $nominal, $harga, $stok]);
            $_SESSION['success'] = 'Produk berhasil ditambahkan!';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Gagal menambah produk: ' . $e->getMessage();
        }
        header('Location: ../pages/admin/products.php');
        exit;
    }

    // Edit Product
    if ($action === 'edit_product') {
        $id_produk = intval($_POST['id_produk'] ?? 0);
        $nama_produk = trim($_POST['nama_produk'] ?? '');
        $nominal = trim($_POST['nominal'] ?? '');
        $harga = intval($_POST['harga'] ?? 0);
        $stok = intval($_POST['stok'] ?? 999);

        if ($id_produk <= 0 || empty($nama_produk) || empty($nominal) || $harga <= 0) {
            $_SESSION['error'] = 'Data produk edit tidak lengkap!';
            header('Location: ../pages/admin/products.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET nama_produk = ?, nominal = ?, harga = ?, stok = ? 
                WHERE id_produk = ?
            ");
            $stmt->execute([$nama_produk, $nominal, $harga, $stok, $id_produk]);
            $_SESSION['success'] = 'Produk berhasil diperbarui!';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Gagal memperbarui produk: ' . $e->getMessage();
        }
        header('Location: ../pages/admin/products.php');
        exit;
    }
}

// GET Requests (Delete / Update Status)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Delete Product
    if ($action === 'delete_product') {
        $id_produk = intval($_GET['id_produk'] ?? 0);
        if ($id_produk > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id_produk = ?");
                $stmt->execute([$id_produk]);
                $_SESSION['success'] = 'Produk berhasil dihapus!';
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Gagal menghapus produk: ' . $e->getMessage();
            }
        }
        header('Location: ../pages/admin/products.php');
        exit;
    }

    // Update Transaction Status
    if ($action === 'update_status') {
        $id_transaksi = intval($_GET['id_transaksi'] ?? 0);
        $status = $_GET['status'] ?? '';

        if ($id_transaksi > 0 && in_array($status, ['sukses', 'gagal'])) {
            try {
                // If transitioning to failed from pending/sukses, we restore the stock of products in that transaction
                // (Note: stock was automatically decreased when detail was inserted. If transaction fails, we increase it back)
                if ($status === 'gagal') {
                    // Start transaction
                    $pdo->beginTransaction();

                    // Get current status of transaction
                    $stmt = $pdo->prepare("SELECT status FROM transactions WHERE id_transaksi = ?");
                    $stmt->execute([$id_transaksi]);
                    $current_status = $stmt->fetchColumn();

                    if ($current_status !== 'gagal') {
                        // Get transaction details
                        $stmt = $pdo->prepare("SELECT id_produk, qty FROM detail_transaksi WHERE id_transaksi = ?");
                        $stmt->execute([$id_transaksi]);
                        $details = $stmt->fetchAll();

                        // Restore stock
                        foreach ($details as $detail) {
                            $stmt = $pdo->prepare("UPDATE products SET stok = stok + ? WHERE id_produk = ?");
                            $stmt->execute([$detail['qty'], $detail['id_produk']]);
                        }
                    }

                    // Update status
                    $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id_transaksi = ?");
                    $stmt->execute([$status, $id_transaksi]);

                    $pdo->commit();
                } else {
                    // Update status to sukses
                    $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id_transaksi = ?");
                    $stmt->execute([$status, $id_transaksi]);
                }

                $_SESSION['success'] = 'Status transaksi #' . $id_transaksi . ' berhasil diubah menjadi ' . strtoupper($status) . '.';
            } catch (Exception $e) {
                if ($status === 'gagal') {
                    $pdo->rollBack();
                }
                $_SESSION['error'] = 'Gagal memperbarui status transaksi: ' . $e->getMessage();
            }
        }
        header('Location: ../pages/admin/transactions.php');
        exit;
    }
}

header('Location: ../pages/admin/dashboard.php');
exit;
?>
