<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once '../config/database.php';

// Check auth
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu!';
    header('Location: ../pages/login.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$metode_pembayaran = $_POST['metode_pembayaran'] ?? '';

if (empty($cart)) {
    $_SESSION['error'] = 'Keranjang belanja Anda kosong!';
    header('Location: ../index.php');
    exit;
}

if (empty($metode_pembayaran)) {
    $_SESSION['error'] = 'Pilih metode pembayaran!';
    header('Location: ../pages/checkout.php');
    exit;
}

try {
    // Start Transaction
    $pdo->beginTransaction();

    $id_user = $_SESSION['user']['id_user'];

    // Group cart items by account and zone ID to split into separate transactions if different
    $grouped_cart = [];
    foreach ($cart as $item) {
        $key = $item['game_account_id'] . '|' . $item['game_zone_id'];
        if (!isset($grouped_cart[$key])) {
            $grouped_cart[$key] = [
                'game_account_id' => $item['game_account_id'],
                'game_zone_id' => $item['game_zone_id'],
                'items' => []
            ];
        }
        $grouped_cart[$key]['items'][] = $item;
    }

    // Process each group as a transaction
    foreach ($grouped_cart as $group) {
        // First, calculate total_harga and gather detailed product info
        $total_harga = 0;
        $items_to_insert = [];

        foreach ($group['items'] as $item) {
            $stmt = $pdo->prepare("SELECT harga, stok FROM products WHERE id_produk = ?");
            $stmt->execute([$item['id_produk']]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception("Produk dengan ID " . $item['id_produk'] . " tidak ditemukan.");
            }

            if ($product['stok'] < $item['qty']) {
                throw new Exception("Stok untuk produk tidak mencukupi (Tersisa: " . $product['stok'] . ").");
            }

            $subtotal = $product['harga'] * $item['qty'];
            $total_harga += $subtotal;
            $items_to_insert[] = [
                'id_produk' => $item['id_produk'],
                'qty' => $item['qty'],
                'subtotal' => $subtotal
            ];
        }

        // Insert transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions (id_user, game_account_id, game_zone_id, total_harga, status, metode_pembayaran)
            VALUES (?, ?, ?, ?, 'pending', ?)
        ");
        $stmt->execute([
            $id_user,
            $group['game_account_id'],
            !empty($group['game_zone_id']) ? $group['game_zone_id'] : null,
            $total_harga,
            $metode_pembayaran
        ]);
        
        $id_transaksi = $pdo->lastInsertId();

        // Insert details (This will fire the DB trigger to decrease stock automatically!)
        foreach ($items_to_insert as $detail) {
            $stmt = $pdo->prepare("
                INSERT INTO detail_transaksi (id_transaksi, id_produk, qty, subtotal)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $id_transaksi,
                $detail['id_produk'],
                $detail['qty'],
                $detail['subtotal']
            ]);
        }
    }

    // Commit Transaction
    $pdo->commit();

    // Clear cart
    unset($_SESSION['cart']);

    $_SESSION['success'] = 'Transaksi berhasil dibuat! Silakan hubungi admin atau tunggu hingga dikonfirmasi.';
    header('Location: ../pages/history.php');
    exit;

} catch (Exception $e) {
    // Rollback transaction on failure
    $pdo->rollBack();
    $_SESSION['error'] = 'Gagal memproses pesanan: ' . $e->getMessage();
    header('Location: ../pages/cart.php');
    exit;
}
?>
