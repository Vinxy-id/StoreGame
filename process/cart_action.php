<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once '../config/database.php';

$action = $_GET['action'] ?? '';

if ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../index.php');
        exit;
    }

    $id_produk = intval($_POST['id_produk'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);
    $game_account_id = trim($_POST['game_account_id'] ?? '');
    $game_zone_id = trim($_POST['game_zone_id'] ?? '');
    $id_game = intval($_POST['id_game'] ?? 0);

    if ($id_produk <= 0 || $qty <= 0 || empty($game_account_id)) {
        $_SESSION['error'] = 'Data pembelian tidak lengkap!';
        header('Location: ../pages/product.php?id_game=' . $id_game);
        exit;
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the exact same product and account ID is already in the cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id_produk'] === $id_produk && $item['game_account_id'] === $game_account_id && $item['game_zone_id'] === $game_zone_id) {
            $item['qty'] += $qty;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'id_produk' => $id_produk,
            'qty' => $qty,
            'game_account_id' => $game_account_id,
            'game_zone_id' => $game_zone_id
        ];
    }

    $_SESSION['success'] = 'Item berhasil ditambahkan ke keranjang!';
    header('Location: ../pages/cart.php');
    exit;
}

if ($action === 'remove') {
    $index = intval($_GET['index'] ?? -1);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // re-index
        $_SESSION['success'] = 'Item berhasil dihapus dari keranjang.';
    }
    header('Location: ../pages/cart.php');
    exit;
}

if ($action === 'clear') {
    unset($_SESSION['cart']);
    $_SESSION['success'] = 'Keranjang belanja dikosongkan.';
    header('Location: ../pages/cart.php');
    exit;
}

header('Location: ../index.php');
exit;
?>
