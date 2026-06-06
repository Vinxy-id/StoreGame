<?php
include '../includes/header.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}
?>

<div class="auth-container" style="max-width: 500px;">
    <div class="panel">
        <h2 class="panel-title" style="justify-content: center;">
            <i class="fa-solid fa-user-plus" style="color: var(--accent-primary);"></i> Daftar Akun <span>Baru</span>
        </h2>
        <form action="../process/auth.php?action=register" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan username unik" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="contoh@domain.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="no_hp" class="form-label">Nomor HP / WhatsApp</label>
                <input type="text" id="no_hp" name="no_hp" class="form-input" placeholder="08xxxxxxxxxx" required autocomplete="tel">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="Buat password aman" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Ketik ulang password" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                Daftar <i class="fa-solid fa-user-check"></i>
            </button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-secondary); font-size: 0.9rem;">
            Sudah punya akun? <a href="login.php" style="color: var(--accent-primary); font-weight: 600;">Login Disini</a>
        </p>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
