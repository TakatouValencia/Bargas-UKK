<?php
/**
 * Konfigurasi Koneksi Database MySQL (XAMPP)
 * Bagas Laundry Express
 */

// Konfigurasi default XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_bagas_laundry');

// Base URL helper jika diperlukan
if (!defined('BASE_URL')) {
    // Mendeteksi base path secara otomatis
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Cari posisi folder proyek
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Mengambil root folder proyek
    $parts = explode('/', trim($script_dir, '/'));
    $project_folder = $parts[0] ?? '';
    
    // Tentukan URL root
    if ($project_folder !== '' && !in_array($project_folder, ['auth', 'admin', 'config', 'assets'])) {
        define('BASE_URL', $protocol . "://" . $host . "/" . $project_folder);
    } else {
        define('BASE_URL', $protocol . "://" . $host);
    }
}

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Tampilan ramah bila database belum di-import di phpMyAdmin
    die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 25px; border: 1px solid #fed7aa; background: #fffbeb; border-radius: 12px; color: #9a3412;'>
            <h2 style='margin-top: 0; color: #ea580c;'>⚠️ Gagal Terhubung ke Database MySQL</h2>
            <p><strong>Pesan Sistem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <hr style='border: none; border-top: 1px solid #fed7aa; margin: 15px 0;'>
            <p><strong>Petunjuk Singkat Pengaturan XAMPP:</strong></p>
            <ol style='line-height: 1.6;'>
                <li>Buka <strong>XAMPP Control Panel</strong> dan pastikan tombol <strong>Apache</strong> dan <strong>MySQL</strong> dalam keadaan <strong>Start (Hijau)</strong>.</li>
                <li>Buka browser Anda, kunjungi: <a href='http://localhost/phpmyadmin' target='_blank' style='color: #059669; font-weight: bold;'>http://localhost/phpmyadmin</a></li>
                <li>Buat database baru bernama <code>db_bagas_laundry</code></li>
                <li>Pilih tab <strong>Import</strong>, lalu pilih file <code>database.sql</code> yang berada di dalam folder proyek ini, dan klik <strong>Import / Kirim</strong>.</li>
                <li>Setelah selesai, silakan refresh halaman ini.</li>
            </ol>
        </div>
    ");
}
