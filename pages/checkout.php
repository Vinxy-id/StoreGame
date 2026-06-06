<?php
require_once '../config/database.php';
include '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu!';
    header('Location: login.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$cart_details = [];
$total_harga = 0;

if (empty($cart)) {
    $_SESSION['error'] = 'Keranjang belanja Anda kosong!';
    header('Location: ../index.php');
    exit;
}

try {
    foreach ($cart as $index => $item) {
        $stmt = $pdo->prepare("
            SELECT p.*, g.nama_game 
            FROM products p 
            INNER JOIN games g ON p.id_game = g.id_game 
            WHERE p.id_produk = ?
        ");
        $stmt->execute([$item['id_produk']]);
        $product = $stmt->fetch();

        if ($product) {
            $subtotal = $product['harga'] * $item['qty'];
            $total_harga += $subtotal;
            $cart_details[] = [
                'nama_game' => $product['nama_game'],
                'nama_produk' => $product['nama_produk'],
                'nominal' => $product['nominal'],
                'harga' => $product['harga'],
                'qty' => $item['qty'],
                'game_account_id' => $item['game_account_id'],
                'game_zone_id' => $item['game_zone_id'],
                'subtotal' => $subtotal
            ];
        }
    }
} catch (PDOException $e) {
    echo '<div class="panel alert" style="background: rgba(255, 23, 68, 0.1); border-color: var(--danger); color: var(--danger);">' . $e->getMessage() . '</div>';
}
?>

<div class="product-layout">
    <!-- Left Column: Checkout Summary -->
    <div class="panel">
        <h3 class="panel-title">
            <i class="fa-solid fa-file-invoice" style="color: var(--accent-primary);"></i> Ringkasan <span>Pesanan</span>
        </h3>
        
        <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
            <?php foreach ($cart_details as $item): ?>
                <div style="background: var(--bg-secondary); border: 1px solid var(--glass-border); padding: 1rem; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="color: var(--accent-primary);"><?= htmlspecialchars($item['nama_game']) ?></strong>
                        <div style="font-size: 0.95rem; margin-top: 0.25rem;">
                            <?= htmlspecialchars($item['nama_produk']) ?> &times; <?= $item['qty'] ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">
                            ID: <code><?= htmlspecialchars($item['game_account_id']) ?></code> 
                            <?php if (!empty($item['game_zone_id'])): ?>
                                (<code><?= htmlspecialchars($item['game_zone_id']) ?></code>)
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="font-weight: 600;">
                        Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="border-top: 1px solid var(--glass-border); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <span style="color: var(--text-secondary); font-size: 1.1rem;">Total Tagihan:</span>
            <strong style="font-size: 1.6rem; color: var(--accent-primary);">Rp <?= number_format($total_harga, 0, ',', '.') ?></strong>
        </div>
    </div>

    <!-- Right Column: Payment & Order Submission -->
    <div>
        <form action="../process/order.php" method="POST">
            <div class="panel">
                <h3 class="panel-title">
                    <i class="fa-solid fa-credit-card" style="color: var(--accent-primary);"></i> Pilih Metode <span>Pembayaran</span>
                </h3>
                
                <div class="payment-grid" style="margin-bottom: 2rem;">
                    <!-- QRIS -->
                    <div class="payment-item active">
                        <input type="radio" name="metode_pembayaran" value="QRIS" checked required>
                        <i class="fa-solid fa-qrcode" style="font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--accent-primary);"></i>
                        <div>QRIS (E-Wallet)</div>
                    </div>
                    
                    <!-- GoPay -->
                    <div class="payment-item">
                        <input type="radio" name="metode_pembayaran" value="GoPay" required>
                        <i class="fa-solid fa-wallet" style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #00a5cf;"></i>
                        <div>GoPay</div>
                    </div>
                    
                    <!-- ShopeePay -->
                    <div class="payment-item">
                        <input type="radio" name="metode_pembayaran" value="ShopeePay" required>
                        <i class="fa-solid fa-mobile-screen-button" style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #ee4d2d;"></i>
                        <div>ShopeePay</div>
                    </div>
                    
                    <!-- Transfer Bank -->
                    <div class="payment-item">
                        <input type="radio" name="metode_pembayaran" value="Transfer Bank" required>
                        <i class="fa-solid fa-building-columns" style="font-size: 1.5rem; margin-bottom: 0.5rem; color: var(--accent-primary);"></i>
                        <div>Transfer Bank</div>
                    </div>
                </div>

                <div style="background: rgba(138, 43, 226, 0.05); border: 1px solid var(--accent-purple-glow); border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5;">
                    <i class="fa-solid fa-circle-info" style="color: var(--accent-primary);"></i> 
                    Pembayaran ini bersifat <strong>simulasi</strong> untuk keperluan praktikum. Setelah Anda menekan tombol "Bayar Sekarang", transaksi akan berstatus <strong>Pending</strong>. Masuk ke halaman admin untuk menyetujui transaksi ini agar stok produk berkurang.
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1.1rem; border-radius: var(--radius-md);">
                    Bayar Sekarang <i class="fa-solid fa-circle-check"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
