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
    <div class="hero-content">
        <h1>An online Marketplace for <span>Buyer and Seller</span></h1>
        <p>Penuhi kebutuhan game favoritmu mulai dari Diamond, Voucher, hingga Crystals dengan harga termurah dan proses otomatis 24/7.</p>
        
        <!-- Search Bar -->
        <form action="" method="GET" style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
            <input type="text" name="search" class="form-input" placeholder="Cari game favorit Anda..." value="<?= htmlspecialchars($search) ?>" style="border-radius: 50px;">
            <button type="submit" class="btn btn-primary" style="border-radius: 50px; padding: 0.5rem 1.5rem;">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>
        </form>
    </div>
    <div class="hero-image">
        <!-- Placeholder for hero image -->
        <i class="fa-solid fa-gamepad" style="font-size: 10rem; color: var(--accent-primary);"></i>
    </div>
</div>

<!-- Marquee Strip -->
<div class="marquee-container">
    <div class="marquee-content">
        <span>Top-Up</span><span>✳</span><span>Voucher</span><span>✳</span><span>Gift Card</span><span>✳</span>
        <span>Top-Up</span><span>✳</span><span>Voucher</span><span>✳</span><span>Gift Card</span><span>✳</span>
        <span>Top-Up</span><span>✳</span><span>Voucher</span><span>✳</span><span>Gift Card</span><span>✳</span>
        <span>Top-Up</span><span>✳</span><span>Voucher</span><span>✳</span><span>Gift Card</span><span>✳</span>
    </div>
</div>

<div class="panel">
    <h2 class="panel-title">
        <i class="fa-solid fa-fire" style="color: var(--accent-primary);"></i> Daftar <span>Game Populer</span>
    </h2>
    
    <?php if (empty($games)): ?>
        <p style="text-align: center; color: var(--text-secondary); margin: 2rem 0;">Game tidak ditemukan.</p>
    <?php else: ?>
        <div class="game-grid">
            <?php foreach ($games as $game): ?>
                <div class="game-card">
                    <div style="height: 180px; position: relative; overflow: hidden; border-bottom: 1px solid var(--glass-border);">
                        <img src="<?= $base_url ?>assets/img/games/<?= htmlspecialchars($game['logo'] ?? 'default_game.png') ?>" alt="<?= htmlspecialchars($game['nama_game']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: var(--transition);">
                        <span style="position: absolute; bottom: 10px; right: 10px; font-size: 0.75rem; background: rgba(11, 26, 20, 0.8); color: var(--accent-primary); padding: 2px 8px; border-radius: 20px; border: 1px solid var(--glass-border); backdrop-filter: blur(4px);">
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
