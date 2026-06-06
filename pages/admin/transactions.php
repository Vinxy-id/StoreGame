<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Check auth & admin role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['error'] = 'Akses ditolak! Anda bukan admin.';
    header('Location: ../../index.php');
    exit;
}

$transactions = [];

try {
    // Fetch all transactions using the VIEW (view_laporan_transaksi)
    $stmt = $pdo->query("
        SELECT * FROM view_laporan_transaksi 
        ORDER BY tgl_transaksi DESC
    ");
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
                'username' => $row['username'],
                'email' => $row['email'],
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
        <i class="fa-solid fa-list-check" style="color: var(--accent-cyan);"></i> Kelola <span>Seluruh Transaksi Masuk</span>
    </h2>
    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
        Sebagai admin, Anda dapat memantau pembayaran dan mengubah status transaksi dari **Pending** menjadi **Sukses** atau **Gagal** (memicu trigger pengembalian stok jika gagal).
    </p>

    <?php if (empty($transactions)): ?>
        <p style="color: var(--text-secondary); text-align: center; margin: 2rem 0;">Belum ada riwayat transaksi pelanggan.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php foreach ($transactions as $tx): ?>
                <div style="background: var(--bg-secondary); border: 1px solid <?= ($tx['status'] === 'pending') ? 'var(--accent-purple-glow)' : 'var(--glass-border)' ?>; border-radius: var(--radius-md); padding: 1.5rem; position: relative;">
                    
                    <?php if ($tx['status'] === 'pending'): ?>
                        <div style="position: absolute; top: -1px; left: 20px; background: var(--accent-purple); color: white; padding: 2px 10px; font-size: 0.75rem; font-weight: bold; border-radius: 0 0 8px 8px; text-transform: uppercase;">
                            Menunggu Konfirmasi
                        </div>
                    <?php endif; ?>

                    <!-- Header of Transaction Block -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 1rem; margin-bottom: 1rem; margin-top: 0.5rem;">
                        <div>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Pelanggan</span>
                            <div style="font-weight: 600; font-size: 0.95rem; margin-top: 0.25rem; color: var(--accent-cyan);">
                                <?= htmlspecialchars($tx['username']) ?>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($tx['email']) ?></span>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Tanggal & Metode</span>
                            <div style="font-weight: 500; font-size: 0.95rem; margin-top: 0.25rem;">
                                <?= date('d M Y, H:i', strtotime($tx['tgl_transaksi'])) ?> WIB
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($tx['metode_pembayaran']) ?></span>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Game & Tujuan</span>
                            <div style="font-weight: 600; font-size: 0.95rem; margin-top: 0.25rem; color: var(--text-primary);">
                                <?= htmlspecialchars($tx['nama_game']) ?>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                ID: <code><?= htmlspecialchars($tx['game_account_id']) ?></code><?= !empty($tx['game_zone_id']) ? ' (<code>' . htmlspecialchars($tx['game_zone_id']) . '</code>)' : '' ?>
                            </span>
                        </div>
                        <div>
                            <span style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">Status</span>
                            <span class="status-badge status-<?= $tx['status'] ?>"><?= $tx['status'] ?></span>
                        </div>
                    </div>

                    <!-- Items inside this Transaction -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
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

                    <!-- Footer / Actions of Transaction Block -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 1rem; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <span style="color: var(--text-secondary); font-weight: 600;">Total Belanja:</span>
                            <strong style="color: var(--accent-cyan); font-size: 1.3rem; margin-left: 0.5rem;">Rp <?= number_format($tx['total'], 0, ',', '.') ?></strong>
                        </div>
                        
                        <!-- Actions for Pending status -->
                        <?php if ($tx['status'] === 'pending'): ?>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="../../process/admin_action.php?action=update_status&id_transaksi=<?= $tx['id_transaksi'] ?>&status=sukses" class="btn btn-primary" style="background: linear-gradient(135deg, var(--success), #00b0ff); box-shadow: 0 4px 15px rgba(0, 230, 118, 0.2); padding: 0.5rem 1rem; border-radius: 4px;">
                                    <i class="fa-solid fa-check"></i> Setujui
                                </a>
                                <a href="../../process/admin_action.php?action=update_status&id_transaksi=<?= $tx['id_transaksi'] ?>&status=gagal" class="btn btn-danger" style="padding: 0.5rem 1rem; border-radius: 4px;">
                                    <i class="fa-solid fa-xmark"></i> Tolak
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
include '../../includes/footer.php';
?>
