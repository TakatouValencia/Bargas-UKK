<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Memerlukan login pelanggan
require_login('auth/login.php');

$user_id = $_SESSION['user_id'];
$success = '';
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Ambil riwayat pesanan milik user ini
try {
    $stmt = $pdo->prepare("
        SELECT t.*, l.nama_paket, l.jenis 
        FROM transaksi t
        JOIN layanan l ON t.layanan_id = l.id
        WHERE t.user_id = ?
        ORDER BY t.id DESC
    ");
    $stmt->execute([$user_id]);
    $pesanan_list = $stmt->fetchAll();
} catch (Exception $e) {
    die("Gagal memuat riwayat: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Bagas Laundry Express</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand-logo">
                <div class="brand-icon">
                    <i class="fa-solid fa-soap"></i>
                </div>
                <span>Bagas Laundry</span>
            </a>

            <nav class="nav-menu">
                <a href="index.php" class="nav-link">Beranda</a>
                <a href="pesan.php" class="nav-link">Pesan Laundry</a>
                <a href="riwayat.php" class="nav-link active">Riwayat Pesanan</a>
            </nav>

            <div class="nav-auth">
                <div class="user-profile-badge">
                    <i class="fa-solid fa-user-check"></i>
                    <span><?= htmlspecialchars($_SESSION['user_nama']) ?></span>
                </div>
                <a href="auth/logout.php" class="btn btn-sm btn-outline" title="Keluar">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                </a>
            </div>
        </div>
    </header>

    <main class="section" style="padding-top: 3rem; padding-bottom: 5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="section-subtitle">Pelacakan & Riwayat</span>
                <h1 class="section-title">Pesanan Laundry Saya</h1>
            </div>
            <a href="pesan.php" class="btn btn-primary">
                <i class="fa-solid fa-plus-circle"></i> Buat Pesanan Baru
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= $success ?></div>
            </div>
        <?php endif; ?>

        <?php if (empty($pesanan_list)): ?>
            <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: var(--radius-lg); border: 1px dashed var(--border);">
                <div style="width: 70px; height: 70px; background: var(--primary-subtle); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.5rem;">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
                <h3 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem;">Belum Ada Riwayat Pesanan</h3>
                <p style="color: var(--muted); max-width: 450px; margin: 0 auto 1.5rem; font-size: 0.95rem;">
                    Anda belum pernah melakukan pemesanan laundry. Ayo cuci pakaian kotor Anda sekarang juga dengan layanan express kami!
                </p>
                <a href="pesan.php" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-plus"></i> Pesan Laundry Sekarang
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Tanggal Masuk</th>
                            <th>Paket Layanan</th>
                            <th>Berat / Qty</th>
                            <th>Total Tagihan</th>
                            <th>Status Pesanan</th>
                            <th>Pembayaran</th>
                            <th>Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pesanan_list as $row): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary-dark); font-family: monospace; font-size: 0.95rem;">
                                        <?= htmlspecialchars($row['kode_transaksi']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="font-size: 0.88rem; font-weight: 600;"><?= date('d M Y', strtotime($row['tanggal_masuk'])) ?></div>
                                    <small style="color: var(--muted);"><?= date('H:i', strtotime($row['tanggal_masuk'])) ?> WIB</small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_paket']) ?></strong>
                                    <?php if (!empty($row['catatan'])): ?>
                                        <div style="font-size: 0.8rem; color: var(--muted); margin-top: 2px;">
                                            <i class="fa-regular fa-comment-dots"></i> <?= htmlspecialchars($row['catatan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $row['berat_jumlah'] ?> <?= $row['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?>
                                </td>
                                <td>
                                    <strong style="color: var(--secondary);"><?= rupiah($row['total_bayar']) ?></strong>
                                </td>
                                <td>
                                    <?= badge_status_pesanan($row['status_pesanan']) ?>
                                </td>
                                <td>
                                    <?= badge_status_bayar($row['status_pembayaran']) ?>
                                </td>
                                <td>
                                    <?php if ($row['metode_pengambilan'] === 'antar_jemput'): ?>
                                        <span style="font-size: 0.82rem; color: var(--primary-dark); font-weight: 600;">
                                            <i class="fa-solid fa-truck"></i> Antar Jemput
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size: 0.82rem; color: var(--muted); font-weight: 600;">
                                            <i class="fa-solid fa-store"></i> Drop Off Outlet
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="footer-bottom" style="border: none; padding-top: 0;">
            &copy; <?= date('Y') ?> Bagas Laundry Express. Seluruh hak cipta dilindungi.
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
