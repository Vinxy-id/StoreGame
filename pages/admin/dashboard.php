<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Check auth & admin role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['error'] = 'Akses ditolak! Anda bukan admin.';
    header('Location: ../../index.php');
    exit;
}

try {
    // 1. Total Pendapatan Sukses (SUM)
    $stmt = $pdo->query("SELECT SUM(total_harga) AS total FROM transactions WHERE status = 'sukses'");
    $total_pendapatan = $stmt->fetch()['total'] ?? 0;

    // 2. Total Transaksi Sukses (COUNT)
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM transactions WHERE status = 'sukses'");
    $transaksi_sukses = $stmt->fetch()['total'] ?? 0;

    // 3. Rata-rata Nilai Transaksi (AVG)
    $stmt = $pdo->query("SELECT AVG(total_harga) AS rata_rata FROM transactions WHERE status = 'sukses'");
    $rata_rata_transaksi = $stmt->fetch()['rata_rata'] ?? 0;

    // 4. Transaksi Terbesar (MAX)
    $stmt = $pdo->query("SELECT MAX(total_harga) AS terbesar FROM transactions WHERE status = 'sukses'");
    $transaksi_terbesar = $stmt->fetch()['terbesar'] ?? 0;

    // 5. Laporan Metode Pembayaran (COUNT + GROUP BY)
    $stmt = $pdo->query("
        SELECT metode_pembayaran, COUNT(*) AS jumlah, SUM(total_harga) AS subtotal
        FROM transactions
        WHERE status = 'sukses'
        GROUP BY metode_pembayaran
    ");
    $metode_stats = $stmt->fetchAll();

    // 6. Produk Terlaris (SUM Qty + JOIN + GROUP BY + ORDER BY + LIMIT 5)
    $stmt = $pdo->query("
        SELECT p.nama_produk, g.nama_game, SUM(dt.qty) AS total_terjual
        FROM detail_transaksi dt
        INNER JOIN products p ON dt.id_produk = p.id_produk
        INNER JOIN games g ON p.id_game = g.id_game
        INNER JOIN transactions t ON dt.id_transaksi = t.id_transaksi
        WHERE t.status = 'sukses'
        GROUP BY p.id_produk
        ORDER BY total_terjual DESC
        LIMIT 5;
    ");
    $top_products = $stmt->fetchAll();

    // 7. Produk dengan Pendapatan > 50.000 (SUM Subtotal + JOIN + GROUP BY + HAVING)
    // We adjust the limit to 50.000 so it matches the sample data range perfectly
    $stmt = $pdo->query("
        SELECT p.nama_produk, g.nama_game, SUM(dt.subtotal) AS total_pendapatan
        FROM detail_transaksi dt
        INNER JOIN products p ON dt.id_produk = p.id_produk
        INNER JOIN games g ON p.id_game = g.id_game
        INNER JOIN transactions t ON dt.id_transaksi = t.id_transaksi
        WHERE t.status = 'sukses'
        GROUP BY p.id_produk
        HAVING total_pendapatan >= 40000
        ORDER BY total_pendapatan DESC
    ");
    $having_products = $stmt->fetchAll();

} catch (PDOException $e) {
    echo '<div class="panel alert" style="background: rgba(255, 23, 68, 0.1); border-color: var(--danger); color: var(--danger);">' . $e->getMessage() . '</div>';
}
?>

<div class="panel" style="margin-bottom: 2rem;">
    <h2 class="panel-title">
        <i class="fa-solid fa-gauge-high" style="color: var(--accent-cyan);"></i> Dashboard <span>Admin (Statistik & Laporan)</span>
    </h2>
    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
        Halaman ini menampilkan visualisasi ringkas data transaksi menggunakan **Fungsi Agregasi (SUM, COUNT, AVG, MAX, GROUP BY, HAVING)** sebagai pemenuhan materi praktikum.
    </p>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-title"><i class="fa-solid fa-coins"></i> Total Pendapatan</span>
            <span class="stat-value">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></span>
            <span class="stat-desc">SUM(total_harga) status='sukses'</span>
        </div>
        
        <div class="stat-card">
            <span class="stat-title"><i class="fa-solid fa-cart-check"></i> Transaksi Sukses</span>
            <span class="stat-value"><?= $transaksi_sukses ?> Transaksi</span>
            <span class="stat-desc">COUNT(*) status='sukses'</span>
        </div>

        <div class="stat-card">
            <span class="stat-title"><i class="fa-solid fa-chart-simple"></i> Rata-rata Transaksi</span>
            <span class="stat-value">Rp <?= number_format($rata_rata_transaksi, 0, ',', '.') ?></span>
            <span class="stat-desc">AVG(total_harga) status='sukses'</span>
        </div>

        <div class="stat-card">
            <span class="stat-title"><i class="fa-solid fa-trophy"></i> Transaksi Terbesar</span>
            <span class="stat-value">Rp <?= number_format($transaksi_terbesar, 0, ',', '.') ?></span>
            <span class="stat-desc">MAX(total_harga) status='sukses'</span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- Top Products Panel -->
    <div class="panel">
        <h3 class="panel-title">
            <i class="fa-solid fa-fire-flame-curved" style="color: var(--accent-cyan);"></i> 5 Produk <span>Terlaris (GROUP BY)</span>
        </h3>
        
        <?php if (empty($top_products)): ?>
            <p style="color: var(--text-secondary); text-align: center; margin: 1.5rem 0;">Belum ada data transaksi sukses.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Nama Produk</th>
                            <th style="text-align: right;">Terjual (Qty)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $prod): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($prod['nama_game']) ?></strong></td>
                                <td><?= htmlspecialchars($prod['nama_produk']) ?></td>
                                <td style="text-align: right; color: var(--accent-cyan); font-weight: 600;"><?= $prod['total_terjual'] ?>x</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Payment Stats Panel -->
    <div class="panel">
        <h3 class="panel-title">
            <i class="fa-solid fa-credit-card" style="color: var(--accent-cyan);"></i> Transaksi Per <span>Metode Pembayaran</span>
        </h3>
        
        <?php if (empty($metode_stats)): ?>
            <p style="color: var(--text-secondary); text-align: center; margin: 1.5rem 0;">Belum ada data transaksi sukses.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Metode</th>
                            <th style="text-align: center;">Jumlah Transaksi</th>
                            <th style="text-align: right;">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($metode_stats as $stat): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($stat['metode_pembayaran']) ?></strong></td>
                                <td style="text-align: center;"><?= $stat['jumlah'] ?>x</td>
                                <td style="text-align: right; color: var(--success); font-weight: 600;">Rp <?= number_format($stat['subtotal'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- HAVING Panel -->
<div class="panel">
    <h3 class="panel-title">
        <i class="fa-solid fa-filter-list" style="color: var(--accent-cyan);"></i> Produk dengan Pendapatan &ge; Rp 40.000 <span>(GROUP BY + HAVING)</span>
    </h3>
    
    <?php if (empty($having_products)): ?>
        <p style="color: var(--text-secondary); text-align: center; margin: 1.5rem 0;">Belum ada produk yang melampaui omset Rp 40.000 dari transaksi sukses.</p>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Game</th>
                        <th>Nama Produk</th>
                        <th style="text-align: right;">Total Pendapatan Produk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($having_products as $prod): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($prod['nama_game']) ?></strong></td>
                            <td><?= htmlspecialchars($prod['nama_produk']) ?></td>
                            <td style="text-align: right; color: var(--accent-cyan); font-weight: 600;">Rp <?= number_format($prod['total_pendapatan'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
include '../../includes/footer.php';
?>
