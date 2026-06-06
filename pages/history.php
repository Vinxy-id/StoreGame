<?php
require_once '../config/database.php';
include '../includes/header.php';

// Check auth
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu!';
    header('Location: login.php');
    exit;
}

$username = $_SESSION['user']['username'];
$transactions = [];

try {
    // Fetch data using our VIEW (view_laporan_transaksi)
    $stmt = $pdo->prepare("
        SELECT * FROM view_laporan_transaksi 
        WHERE username = ? 
        ORDER BY tgl_transaksi DESC
    ");
    $stmt->execute([$username]);
    $rows = $stmt->fetchAll();

    // Group items by transaction ID
    foreach ($rows as $row) {
        $id = $row['id_transaksi'];
        if (!isset($transactions[$id])) {
            $transactions[$id] = [
                'id_transaksi' => $row['id_transaksi'],
                'tgl_transaksi' => $row['tgl_transaksi'],
                'game_account_id' => $row['game_account_id'],
                'game_zone_id' => $row['game_zone_id'],
                'metode_pembayaran' => $row['metode_pembayaran'],
                'status' => $row['status'],
                'nama_game' => $row['nama_game'],
                'items' => [],
                'total' => 0
            ];
        }
        $transactions[$id]['items'][] = [
            'nama_produk' => $row['nama_produk'],
            'nominal' => $row['nominal'],
            'harga_satuan' => $row['harga_satuan'],
            'qty' => $row['qty'],
            'subtotal' => $row['subtotal']
        ];
        $transactions[$id]['total'] += $row['subtotal'];
    }
} catch (PDOException $e) {
    echo '<div class="panel alert" style="background: rgba(255, 23, 68, 0.1); border-color: var(--danger); color: var(--danger);">' . $e->getMessage() . '</div>';
}
?>

<div class="panel">
    <h2 class="panel-title">
        <i class="fa-solid fa-clock-rotate-left" style="color: var(--accent-cyan);"></i> Riwayat <span>Transaksi Anda</span>
    </h2>

    <?php if (empty($transactions)): ?>
        <div style="text-align: center; padding: 3rem 1rem;">
            <i class="fa-solid fa-receipt" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1.5rem;"></i>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Anda belum pernah melakukan transaksi.</p>
            <a href="../index.php" class="btn btn-primary"><i class="fa-solid fa-circle-plus"></i> Mulai Top Up Pertama</a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php foreach ($transactions as $tx): ?>
                <div style="background: var(--bg-secondary); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 1.5rem; transition: var(--transition);">
                    <!-- Header of Transaction Block -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 1rem; margin-bottom: 1rem;">
                        <div>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Tanggal Transaksi</span>
                            <div style="font-weight: 500; font-size: 0.95rem; margin-top: 0.25rem;">
                                <?= date('d M Y, H:i', strtotime($tx['tgl_transaksi'])) ?> WIB
                            </div>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Game & Tujuan</span>
                            <div style="font-weight: 600; font-size: 0.95rem; margin-top: 0.25rem; color: var(--text-primary);">
                                <?= htmlspecialchars($tx['nama_game']) ?> (ID: <code><?= htmlspecialchars($tx['game_account_id']) ?></code><?= !empty($tx['game_zone_id']) ? ' Zone: <code>' . htmlspecialchars($tx['game_zone_id']) . '</code>' : '' ?>)
                            </div>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Metode</span>
                            <div style="font-weight: 500; font-size: 0.95rem; margin-top: 0.25rem; color: var(--accent-cyan);">
                                <?= htmlspecialchars($tx['metode_pembayaran']) ?>
                            </div>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">Status</span>
                            <span class="status-badge status-<?= $tx['status'] ?>"><?= $tx['status'] ?></span>
                        </div>
                    </div>

                    <!-- Items inside this Transaction -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1rem;">
                        <?php foreach ($tx['items'] as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem;">
                                <div style="color: var(--text-secondary);">
                                    <?= htmlspecialchars($item['nama_produk']) ?> (<?= htmlspecialchars($item['nominal']) ?>) &times; <?= $item['qty'] ?>
                                </div>
                                <div style="font-weight: 500;">
                                    Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Footer of Transaction Block -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 1rem; font-weight: 700;">
                        <span style="color: var(--text-secondary);">Total Belanja:</span>
                        <span style="color: var(--accent-cyan); font-size: 1.25rem;">Rp <?= number_format($tx['total'], 0, ',', '.') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
include '../includes/footer.php';
?>
