<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once '../config/database.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Username dan Password wajib diisi!';
            header('Location: ../pages/register.php');
            exit;
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Konfirmasi password tidak cocok!';
            header('Location: ../pages/register.php');
            exit;
        }

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Username sudah terdaftar!';
            header('Location: ../pages/register.php');
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, no_hp, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->execute([$username, $hashed_password, $email, $no_hp]);
            
            $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
            header('Location: ../pages/login.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
            header('Location: ../pages/register.php');
            exit;
        }
    }

    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Username dan Password wajib diisi!';
            header('Location: ../pages/login.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            $password_correct = false;
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $password_correct = true;
                } elseif ($password === $user['password']) {
                    $password_correct = true;
                }
            }

            if ($user && $password_correct) {
                $_SESSION['user'] = [
                    'id_user' => $user['id_user'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                $_SESSION['success'] = 'Selamat datang kembali, ' . $user['username'] . '!';
                
                // If admin, go to admin dashboard, else homepage
                if ($user['role'] === 'admin') {
                    header('Location: ../pages/admin/dashboard.php');
                } else {
                    header('Location: ../index.php');
                }
                exit;
            } else {
                $_SESSION['error'] = 'Username atau Password salah!';
                header('Location: ../pages/login.php');
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
            header('Location: ../pages/login.php');
            exit;
        }
    }
}

if ($action === 'logout') {
    session_destroy();
    session_start(); // restart session to hold success message
    $_SESSION['success'] = 'Anda telah berhasil logout.';
    header('Location: ../index.php');
    exit;
}

header('Location: ../index.php');
exit;
?>
