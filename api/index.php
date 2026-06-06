<?php
// api/index.php
// Vercel Serverless PHP Entrypoint Router

$uri = $_SERVER['REQUEST_URI'];
// Hapus query string jika ada (misal: /pages/product.php?id_game=1 -> /pages/product.php)
$uri = explode('?', $uri)[0];

// Blokir akses langsung ke folder internal demi keamanan
if (preg_match('/^\/(config|includes|database)\//', $uri)) {
    http_response_code(403);
    echo "403 Forbidden - Akses langsung ke direktori ini tidak diizinkan.";
    exit;
}

$base_path = dirname(__DIR__);

// Tentukan target file PHP yang ingin dijalankan
if ($uri === '/' || $uri === '') {
    $target = $base_path . '/index.php';
} else {
    $target = $base_path . $uri;
    // Jika uri mengarah ke folder, cari index.php di dalamnya
    if (is_dir($target)) {
        $target = rtrim($target, '/') . '/index.php';
    }
    // Jika file tidak memiliki ekstensi .php tapi filenya ada dengan ekstensi .php
    if (!file_exists($target) && file_exists($target . '.php')) {
        $target .= '.php';
    }
}

// Jalankan file jika ditemukan
if (file_exists($target) && is_file($target)) {
    // Palsukan (Spoof) $_SERVER variables agar link navigasi, CSS, dan active tab tetap bekerja dengan benar
    $script_name = str_replace($base_path, '', $target);
    $_SERVER['SCRIPT_NAME'] = '/' . ltrim(str_replace('\\', '/', $script_name), '/');
    $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
    $_SERVER['SCRIPT_FILENAME'] = $target;

    // Ubah working directory ke folder file target agar relative path (include/require) bekerja
    chdir(dirname($target));
    
    // Jalankan file target
    require $target;
} else {
    http_response_code(404);
    echo "404 Not Found - Halaman tidak ditemukan.";
}
?>
