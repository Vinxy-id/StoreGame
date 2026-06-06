<?php
require_once 'config/database.php';
include 'includes/header.php';

// Fetch games with count of products using LEFT JOIN and GROUP BY
try {
    $search = $_GET['search'] ?? '';
    if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT g.*, COUNT(p.id_produk) AS total_produk
            FROM games g
            LEFT JOIN products p ON g.id_game = p.id_game
            WHERE LOWER(g.nama_game) LIKE LOWER(?)
            GROUP BY g.id_game
        ");
        $stmt->execute(['%' . $search . '%']);
    } else {
        $stmt = $pdo->query("
            SELECT g.*, COUNT(p.id_produk) AS total_produk
            FROM games g
            LEFT JOIN products p ON g.id_game = p.id_game
            GROUP BY g.id_game
        ");
    }
    $games = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '<div class="panel alert" style="background: rgba(255, 23, 68, 0.1); border-color: var(--danger); color: var(--danger);">' . $e->getMessage() . '</div>';
    $games = [];
}
?>

<div class="hero">
    <h1>Top Up Game Tercepat & <span>Terpercaya</span></h1>
    <p>Penuhi kebutuhan game favoritmu mulai dari Diamond, Voucher, hingga Crystals dengan harga termurah dan proses otomatis 24/7.</p>
    
    <!-- Search Bar -->
    <form action="" method="GET" style="max-width: 500px; margin: 0 auto; display: flex; gap: 0.5rem;">
        <input type="text" name="search" class="form-input" placeholder="Cari game favorit Anda..." value="<?= htmlspecialchars($search) ?>" style="border-radius: 50px;">
        <button type="submit" class="btn btn-primary" style="border-radius: 50px; padding: 0.5rem 1.5rem;">
            <i class="fa-solid fa-magnifying-glass"></i> Cari
        </button>
    </form>
</div>

<div class="panel">
    <h2 class="panel-title">
        <i class="fa-solid fa-fire" style="color: var(--accent-cyan);"></i> Daftar <span>Game Populer</span>
    </h2>
    
    <?php if (empty($games)): ?>
        <p style="text-align: center; color: var(--text-secondary); margin: 2rem 0;">Game tidak ditemukan.</p>
    <?php else: ?>
        <div class="game-grid">
            <?php foreach ($games as $game): ?>
                <div class="game-card">
                    <!-- We'll simulate game images with Font Awesome icons or colors if images are missing, but let's make it look premium -->
                    <div style="height: 180px; background: linear-gradient(135deg, var(--bg-secondary), var(--bg-primary)); display: flex; flex-direction: column; justify-content: center; align-items: center; border-bottom: 1px solid var(--glass-border); position: relative;">
                        <i class="fa-solid fa-gamepad" style="font-size: 4rem; color: var(--accent-purple); opacity: 0.3;"></i>
                        <span style="position: absolute; bottom: 10px; right: 10px; font-size: 0.75rem; background: rgba(0, 255, 255, 0.1); color: var(--accent-cyan); padding: 2px 8px; border-radius: 20px; border: 1px solid rgba(0, 255, 255, 0.2);">
                            <?= $game['total_produk'] ?> Item
                        </span>
                    </div>
                    <div class="game-card-content">
                        <div class="game-publisher"><?= htmlspecialchars($game['publisher']) ?></div>
                        <h3 class="game-title"><?= htmlspecialchars($game['nama_game']) ?></h3>
                        <a href="pages/product.php?id_game=<?= $game['id_game'] ?>" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            Top Up <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
include 'includes/footer.php';
?>
