<?php
require_once '../config/auth.php';

// Hapus semua variabel sesi
$_SESSION = [];

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Redirect ke login dengan pesan
session_start();
$_SESSION['flash_success'] = 'Anda telah berhasil keluar (logout).';
header('Location: login.php');
exit;
