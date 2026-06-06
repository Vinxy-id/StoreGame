<?php
include '../includes/header.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
?>

<div class="auth-container">
    <div class="panel">
        <h2 class="panel-title" style="justify-content: center;">
            <i class="fa-solid fa-right-to-bracket" style="color: var(--accent-cyan);"></i> Login Ke <span>TopUpin</span>
        </h2>
        <form action="../process/auth.php?action=login" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan username Anda" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password Anda" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                Masuk <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-secondary); font-size: 0.9rem;">
            Belum punya akun? <a href="register.php" style="color: var(--accent-cyan); font-weight: 600;">Daftar Sekarang</a>
        </p>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
