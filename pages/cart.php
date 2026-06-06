<?php
require_once '../config/database.php';
include '../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$cart_details = [];
$total_harga = 0;

if (!empty($cart)) {
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
                    'index' => $index,
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
}
?>

<div class="panel">
    <h2 class="panel-title">
        <i class="fa-solid fa-cart-shopping" style="color: var(--accent-cyan);"></i> Keranjang <span>Belanja</span>
    </h2>

    <?php if (empty($cart_details)): ?>
        <div style="text-align: center; padding: 3rem 1rem;">
            <i class="fa-solid fa-cart-flatbed-empty" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1.5rem;"></i>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Keranjang belanja Anda masih kosong.</p>
            <a href="../index.php" class="btn btn-primary"><i class="fa-solid fa-circle-plus"></i> Mulai Top Up</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Game</th>
                        <th>Item</th>
                        <th>Tujuan (ID)</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_details as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['nama_game']) ?></strong></td>
                            <td><?= htmlspecialchars($item['nama_produk']) ?> (<?= htmlspecialchars($item['nominal']) ?>)</td>
                            <td>
                                <code><?= htmlspecialchars($item['game_account_id']) ?></code>
                                <?php if (!empty($item['game_zone_id'])): ?>
                                    (<code><?= htmlspecialchars($item['game_zone_id']) ?></code>)
                                <?php endif; ?>
                            </td>
                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($item['qty']) ?></td>
                            <td style="color: var(--accent-cyan); font-weight: 600;">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                            <td style="text-align: center;">
                                <a href="../process/cart_action.php?action=remove&index=<?= $item['index'] ?>" class="btn btn-danger" style="padding: 0.35rem 0.75rem; border-radius: 4px; font-size: 0.85rem;">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; border-top: 1px solid var(--glass-border); padding-top: 1.5rem;">
            <div>
                <a href="../process/cart_action.php?action=clear" class="btn btn-outline" style="border-color: var(--danger); color: var(--danger);"><i class="fa-solid fa-broom"></i> Kosongkan Keranjang</a>
            </div>
            
            <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 1rem;">
                <div>
                    <span style="color: var(--text-secondary); font-size: 1.1rem; margin-right: 1rem;">Total Pembayaran:</span>
                    <strong style="font-size: 1.8rem; color: var(--accent-cyan);">Rp <?= number_format($total_harga, 0, ',', '.') ?></strong>
                </div>

                <div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="checkout.php" class="btn btn-primary" style="padding: 0.85rem 2rem; font-size: 1.1rem; border-radius: var(--radius-md);">
                            Lanjut Ke Checkout <i class="fa-solid fa-credit-card"></i>
                        </a>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                            <span style="color: var(--warning); font-size: 0.9rem;"><i class="fa-solid fa-circle-info"></i> Silakan login untuk melakukan checkout.</span>
                            <a href="login.php" class="btn btn-primary" style="padding: 0.85rem 2rem; font-size: 1.1rem; border-radius: var(--radius-md);">
                                Login & Checkout <i class="fa-solid fa-right-to-bracket"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
include '../includes/footer.php';
?>
