<?php
if (!isset($_SESSION)) {
    session_start();
}

// Dynamically determine base URL to support both http://localhost/StoreGame/ and http://storegame.test/
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];

// If in StoreGame folder
$base_dir = strpos($script, '/StoreGame/') !== false ? '/StoreGame/' : '/';
$base_url = $protocol . '://' . $host . $base_dir;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gammy - Store Top Up Game Terpercaya</title>
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
    <!-- Font Awesome for Modern Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
    <div class="nav-container">
        <a href="<?= $base_url ?>" class="logo">
            <i class="fa-solid fa-gamepad" style="color: var(--accent-primary);"></i> Gammy
        </a>
        <ul class="nav-links">
            <li>
                <a href="<?= $base_url ?>" class="<?= ($script == $base_dir . 'index.php' || $script == $base_dir) ? 'active' : '' ?>">
                    <i class="fa-solid fa-house"></i> Home
                </a>
            </li>
            <?php if (isset($_SESSION['user'])): ?>
                <li>
                    <a href="<?= $base_url ?>pages/history.php" class="<?= (strpos($script, 'history.php') !== false) ? 'active' : '' ?>">
                        <i class="fa-solid fa-receipt"></i> Riwayat
                    </a>
                </li>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <li>
                        <a href="<?= $base_url ?>pages/admin/dashboard.php" class="<?= (strpos($script, 'admin/dashboard.php') !== false) ? 'active' : '' ?>">
                            <i class="fa-solid fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base_url ?>pages/admin/products.php" class="<?= (strpos($script, 'admin/products.php') !== false) ? 'active' : '' ?>">
                            <i class="fa-solid fa-boxes-stacked"></i> Produk
                        </a>
                    </li>
                    <li>
                        <a href="<?= $base_url ?>pages/admin/transactions.php" class="<?= (strpos($script, 'admin/transactions.php') !== false) ? 'active' : '' ?>">
                            <i class="fa-solid fa-list-check"></i> Transaksi
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        <div class="nav-actions">
            <!-- Cart Button -->
            <a href="<?= $base_url ?>pages/cart.php" class="cart-badge-container btn btn-outline" style="border-radius: 50px; padding: 0.5rem 1.25rem;">
                <i class="fa-solid fa-cart-shopping"></i> Keranjang
                <?php
                $cart_count = 0;
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $cart_count += $item['qty'];
                    }
                }
                if ($cart_count > 0):
                ?>
                    <span class="badge"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <!-- Auth Button -->
            <?php if (isset($_SESSION['user'])): ?>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="color: var(--text-secondary); font-size: 0.9rem;">
                        Hi, <strong style="color: var(--accent-primary);"><?= htmlspecialchars($_SESSION['user']['username']) ?></strong>
                    </span>
                    <a href="<?= $base_url ?>process/auth.php?action=logout" class="btn btn-danger" style="padding: 0.5rem 1.25rem; border-radius: 50px;">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </div>
            <?php else: ?>
                <a href="<?= $base_url ?>pages/login.php" class="btn btn-primary" style="padding: 0.5rem 1.25rem; border-radius: 50px;">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main>
<?php
// Flash message helper
if (isset($_SESSION['success'])) {
    echo '<div class="panel alert" style="background: rgba(0, 230, 118, 0.1); border-color: var(--success); color: var(--success); margin-bottom: 2rem; padding: 1rem;"><i class="fa-solid fa-circle-check"></i> ' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="panel alert" style="background: rgba(255, 23, 68, 0.1); border-color: var(--danger); color: var(--danger); margin-bottom: 2rem; padding: 1rem;"><i class="fa-solid fa-circle-exclamation"></i> ' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>
