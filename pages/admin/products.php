<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Check auth & admin role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['error'] = 'Akses ditolak! Anda bukan admin.';
    header('Location: ../../index.php');
    exit;
}

$edit_id = intval($_GET['edit'] ?? 0);
$edit_product = null;

try {
    // Fetch all games
    $games = $pdo->query("SELECT * FROM games ORDER BY nama_game ASC")->fetchAll();

    // Fetch all products with their game names using LEFT JOIN (Modul 6 requirement)
    $products = $pdo->query("
        SELECT p.*, g.nama_game 
        FROM products p 
        LEFT JOIN games g ON p.id_game = g.id_game 
        ORDER BY g.nama_game ASC, p.harga ASC
    ")->fetchAll();

    // If editing a product, fetch its current details
    if ($edit_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id_produk = ?");
        $stmt->execute([$edit_id]);
        $edit_product = $stmt->fetch();
    }
} catch (PDOException $e) {
    echo '<div class="panel alert" style="background: rgba(255, 23, 68, 0.1); border-color: var(--danger); color: var(--danger);">' . $e->getMessage() . '</div>';
}
?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
    <!-- LEFT COLUMN: Forms -->
    <div>
        <!-- Edit Product Form (If edit query parameter is active) -->
        <?php if ($edit_product): ?>
            <div class="panel" style="border-color: var(--accent-cyan);">
                <h3 class="panel-title" style="color: var(--accent-cyan);">
                    <i class="fa-solid fa-pen-to-square"></i> Edit <span>Produk</span>
                </h3>
                <form action="../../process/admin_action.php?action=edit_product" method="POST">
                    <input type="hidden" name="id_produk" value="<?= $edit_product['id_produk'] ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Nama Game (Tidak dapat diubah)</label>
                        <input type="text" class="form-input" value="<?php 
                            foreach($games as $g) {
                                if($g['id_game'] == $edit_product['id_game']) echo htmlspecialchars($g['nama_game']);
                            }
                        ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="edit_nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" id="edit_nama_produk" name="nama_produk" class="form-input" value="<?= htmlspecialchars($edit_product['nama_produk']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_nominal" class="form-label">Nominal</label>
                        <input type="text" id="edit_nominal" name="nominal" class="form-input" value="<?= htmlspecialchars($edit_product['nominal']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_harga" class="form-label">Harga (Rp)</label>
                        <input type="number" id="edit_harga" name="harga" class="form-input" value="<?= $edit_product['harga'] ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_stok" class="form-label">Stok Virtual</label>
                        <input type="number" id="edit_stok" name="stok" class="form-input" value="<?= $edit_product['stok'] ?>" min="0" required>
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">
                            Simpan Perubahan
                        </button>
                        <a href="products.php" class="btn btn-outline" style="border-color: var(--text-muted); color: var(--text-secondary);">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Add Product Form -->
        <div class="panel">
            <h3 class="panel-title">
                <i class="fa-solid fa-plus"></i> Tambah <span>Produk Top Up</span>
            </h3>
            <form action="../../process/admin_action.php?action=add_product" method="POST">
                <div class="form-group">
                    <label for="id_game" class="form-label">Pilih Game</label>
                    <select id="id_game" name="id_game" class="form-input" required style="background-color: var(--bg-secondary); color: var(--text-primary);">
                        <option value="">-- Pilih Game --</option>
                        <?php foreach ($games as $game): ?>
                            <option value="<?= $game['id_game'] ?>"><?= htmlspecialchars($game['nama_game']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nama_produk" class="form-label">Nama Produk (Nama Display)</label>
                    <input type="text" id="nama_produk" name="nama_produk" class="form-input" placeholder="Contoh: Weekly Diamond Pass" required>
                </div>

                <div class="form-group">
                    <label for="nominal" class="form-label">Nominal / Kuantitas</label>
                    <input type="text" id="nominal" name="nominal" class="form-input" placeholder="Contoh: 100 Diamond / 1 Bulan" required>
                </div>

                <div class="form-group">
                    <label for="harga" class="form-label">Harga (Rp)</label>
                    <input type="number" id="harga" name="harga" class="form-input" placeholder="Harga jual" min="1" required>
                </div>

                <div class="form-group">
                    <label for="stok" class="form-label">Stok Awal</label>
                    <input type="number" id="stok" name="stok" class="form-input" value="999" min="0" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fa-solid fa-floppy-disk"></i> Tambah Produk
                </button>
            </form>
        </div>

        <!-- Add Game Form -->
        <div class="panel">
            <h3 class="panel-title">
                <i class="fa-solid fa-folder-plus"></i> Tambah <span>Game Baru</span>
            </h3>
            <form action="../../process/admin_action.php?action=add_game" method="POST">
                <div class="form-group">
                    <label for="nama_game" class="form-label">Nama Game</label>
                    <input type="text" id="nama_game" name="nama_game" class="form-input" placeholder="Contoh: Valorant" required>
                </div>

                <div class="form-group">
                    <label for="publisher" class="form-label">Publisher</label>
                    <input type="text" id="publisher" name="publisher" class="form-input" placeholder="Contoh: Riot Games" required>
                </div>

                <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center;">
                    <i class="fa-solid fa-gamepad"></i> Daftarkan Game
                </button>
            </form>
        </div>
    </div>

    <!-- RIGHT COLUMN: Products List -->
    <div class="panel">
        <h3 class="panel-title">
            <i class="fa-solid fa-boxes-stacked" style="color: var(--accent-cyan);"></i> Daftar <span>Seluruh Produk (LEFT JOIN)</span>
        </h3>

        <?php if (empty($products)): ?>
            <p style="color: var(--text-secondary); text-align: center; margin: 2rem 0;">Belum ada produk terdaftar.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Nama Produk</th>
                            <th>Nominal</th>
                            <th>Harga</th>
                            <th style="text-align: center;">Stok</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($prod['nama_game'] ?? 'Game Terhapus') ?></strong></td>
                                <td><?= htmlspecialchars($prod['nama_produk']) ?></td>
                                <td><code><?= htmlspecialchars($prod['nominal']) ?></code></td>
                                <td>Rp <?= number_format($prod['harga'], 0, ',', '.') ?></td>
                                <td style="text-align: center;"><?= $prod['stok'] ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 0.25rem; justify-content: center;">
                                        <a href="products.php?edit=<?= $prod['id_produk'] ?>" class="btn btn-outline" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 4px; border-color: var(--accent-cyan); color: var(--accent-cyan);">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <a href="../../process/admin_action.php?action=delete_product&id_produk=<?= $prod['id_produk'] ?>" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; border-radius: 4px;" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include '../../includes/footer.php';
?>
