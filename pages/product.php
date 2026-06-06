<?php
require_once '../config/database.php';
include '../includes/header.php';

$id_game = $_GET['id_game'] ?? 0;

try {
    // Get game info
    $stmt = $pdo->prepare("SELECT * FROM games WHERE id_game = ?");
    $stmt->execute([$id_game]);
    $game = $stmt->fetch();

    if (!$game) {
        $_SESSION['error'] = 'Game tidak ditemukan!';
        header('Location: ../index.php');
        exit;
    }

    // Get products for this game
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id_game = ? ORDER BY harga ASC");
    $stmt->execute([$id_game]);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="panel alert" style="background: rgba(255, 23, 68, 0.1); border-color: var(--danger); color: var(--danger);">' . $e->getMessage() . '</div>';
    $products = [];
}
?>

<div class="product-layout">
    <!-- Sidebar: Game Info -->
    <div class="product-sidebar">
        <div style="width: 100%; height: 180px; background: linear-gradient(135deg, var(--bg-secondary), var(--bg-primary)); display: flex; flex-direction: column; justify-content: center; align-items: center; border-radius: var(--radius-sm); border: 1px solid var(--glass-border); margin-bottom: 1.5rem;">
            <i class="fa-solid fa-gamepad" style="font-size: 5rem; color: var(--accent-purple); opacity: 0.3;"></i>
        </div>
        <div class="game-publisher" style="font-size: 0.9rem; letter-spacing: 2px;"><?= htmlspecialchars($game['publisher']) ?></div>
        <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 1rem;"><?= htmlspecialchars($game['nama_game']) ?></h2>
        <p style="color: var(--text-secondary); font-size: 0.95rem; text-align: left; line-height: 1.5;">
            Layanan top up game <?= htmlspecialchars($game['nama_game']) ?> tercepat, termurah, dan terpercaya. Masukkan data akun game Anda dengan benar.
        </p>
    </div>

    <!-- Main Content: Inputs & Products -->
    <div>
        <form action="../process/cart_action.php?action=add" method="POST">
            <input type="hidden" name="id_game" value="<?= $game['id_game'] ?>">
            
            <!-- Step 1: Input Data Akun -->
            <div class="panel">
                <h3 class="panel-title">
                    <span>1.</span> Masukkan Data Akun Game
                </h3>
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="game_account_id" class="form-label">User ID (ID Game)</label>
                        <input type="text" id="game_account_id" name="game_account_id" class="form-input" placeholder="Masukkan ID Game Anda" required>
                    </div>
                    
                    <?php if ($game['id_game'] == 1): // Mobile Legends has Zone ID ?>
                        <div class="form-group">
                            <label for="game_zone_id" class="form-label">Zone ID</label>
                            <input type="text" id="game_zone_id" name="game_zone_id" class="form-input" placeholder="Zone ID" required>
                        </div>
                    <?php else: ?>
                        <!-- Hidden or optional zone ID for other games -->
                        <div class="form-group">
                            <label for="game_zone_id" class="form-label">Zone ID (Opsional)</label>
                            <input type="text" id="game_zone_id" name="game_zone_id" class="form-input" placeholder="(Kosongkan)">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 2: Pilih Nominal Top Up -->
            <div class="panel">
                <h3 class="panel-title">
                    <span>2.</span> Pilih Nominal Top Up
                </h3>
                <?php if (empty($products)): ?>
                    <p style="color: var(--text-secondary); text-align: center; margin: 1.5rem 0;">Produk belum tersedia untuk game ini.</p>
                <?php else: ?>
                    <div class="product-grid">
                        <?php foreach ($products as $index => $product): ?>
                            <div class="product-item <?= ($index === 0) ? 'active' : '' ?>">
                                <input type="radio" name="id_produk" value="<?= $product['id_produk'] ?>" <?= ($index === 0) ? 'checked' : '' ?> required>
                                <div class="product-nominal"><?= htmlspecialchars($product['nominal']) ?></div>
                                <div class="product-price">Rp <?= number_format($product['harga'], 0, ',', '.') ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Stok: <?= $product['stok'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Step 3: Jumlah & Checkout -->
            <div class="panel" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                <div>
                    <h3 class="panel-title" style="margin-bottom: 0.5rem; border: none; padding: 0;">
                        <span>3.</span> Jumlah Pembelian
                    </h3>
                    <div class="form-group" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="number" name="qty" class="form-input" value="1" min="1" max="99" style="width: 80px; text-align: center;">
                        <span style="color: var(--text-secondary);">Item</span>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; border-radius: var(--radius-md);">
                        <i class="fa-solid fa-cart-plus"></i> Tambah Ke Keranjang
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
