<?php
/**
 * Helper Session, Otorisasi dan Utilitas Tampilan
 * Bagas Laundry Express
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah user sudah login
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Cek apakah user memiliki role admin
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Proteksi halaman khusus member / login
 */
function require_login($redirect_to = '../auth/login.php') {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Silakan login terlebih dahulu untuk mengakses halaman ini.';
        header("Location: " . $redirect_to);
        exit;
    }
}

/**
 * Proteksi halaman khusus admin
 */
function require_admin($redirect_to = '../auth/login.php') {
    if (!is_admin()) {
        $_SESSION['flash_error'] = 'Akses ditolak! Halaman ini hanya dapat diakses oleh Administrator.';
        header("Location: " . $redirect_to);
        exit;
    }
}

/**
 * Format mata uang Rupiah
 */
function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Format tanggal Indonesia
 */
function tgl_indo($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($tanggal);
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)];
    $thn = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    return "$tgl $bln $thn, $jam WIB";
}

/**
 * Badge status pesanan
 */
function badge_status_pesanan($status) {
    switch ($status) {
        case 'menunggu_konfirmasi':
            return '<span class="badge badge-warning"><i class="fa-solid fa-clock"></i> Menunggu Konfirmasi</span>';
        case 'dijemput':
            return '<span class="badge badge-info"><i class="fa-solid fa-truck"></i> Sedang Dijemput</span>';
        case 'proses_cuci':
            return '<span class="badge badge-primary"><i class="fa-solid fa-soap"></i> Proses Cuci</span>';
        case 'siap_diambil':
            return '<span class="badge badge-accent"><i class="fa-solid fa-box-open"></i> Siap Diambil</span>';
        case 'selesai':
            return '<span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Selesai</span>';
        case 'dibatalkan':
            return '<span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Dibatalkan</span>';
        default:
            return '<span class="badge badge-neutral">' . htmlspecialchars($status) . '</span>';
    }
}

/**
 * Badge status pembayaran
 */
function badge_status_bayar($status) {
    if ($status === 'lunas') {
        return '<span class="badge badge-success"><i class="fa-solid fa-receipt"></i> Lunas</span>';
    }
    return '<span class="badge badge-danger"><i class="fa-solid fa-hourglass-half"></i> Belum Lunas</span>';
}
