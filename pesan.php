<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Memerlukan login untuk memesan
require_login('auth/login.php');

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data layanan dari database
try {
    $stmt = $pdo->query("SELECT * FROM layanan ORDER BY id ASC");
    $layanan_list = $stmt->fetchAll();
} catch (Exception $e) {
    die("Gagal memuat layanan: " . $e->getMessage());
}

// Ambil info pelanggan terbaru
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$current_user = $stmt_user->fetch();

// Pre-selected layanan dari URL query jika ada
$selected_layanan_id = isset($_GET['layanan']) ? (int)$_GET['layanan'] : ($layanan_list[0]['id'] ?? 1);

// Proses Form Pemesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $layanan_id = (int)($_POST['layanan_id'] ?? 0);
    $berat_jumlah = (float)($_POST['berat_jumlah'] ?? 1);
    $metode = $_POST['metode_pengambilan'] ?? 'antar_jemput';
    $alamat_jemput = trim($_POST['alamat_jemput'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    if ($berat_jumlah <= 0) {
        $error = 'Perkiraan berat atau jumlah item minimal 1.';
    } elseif ($metode === 'antar_jemput' && empty($alamat_jemput)) {
        $error = 'Alamat penjemputan wajib diisi jika memilih opsi Antar Jemput.';
    } else {
        // Cari harga layanan terpilih
        $stmt_layanan = $pdo->prepare("SELECT * FROM layanan WHERE id = ?");
        $stmt_layanan->execute([$layanan_id]);
        $layanan = $stmt_layanan->fetch();

        if (!$layanan) {
            $error = 'Paket layanan yang dipilih tidak valid.';
        } else {
            // Hitung total bayar
            $total_bayar = (int)round($layanan['harga'] * $berat_jumlah);

            // Generate Kode Transaksi Unik: TRX-YYYYMMDD-RAND
            $kode_transaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            try {
                $stmt_insert = $pdo->prepare("
                    INSERT INTO transaksi (kode_transaksi, user_id, layanan_id, berat_jumlah, total_bayar, metode_pengambilan, alamat_jemput, catatan, status_pesanan, status_pembayaran, tanggal_masuk)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'menunggu_konfirmasi', 'belum_bayar', NOW())
                ");
                $stmt_insert->execute([
                    $kode_transaksi,
                    $user_id,
                    $layanan_id,
                    $berat_jumlah,
                    $total_bayar,
                    $metode,
                    $metode === 'antar_jemput' ? $alamat_jemput : null,
                    $catatan
                ]);

                $_SESSION['flash_success'] = "Pesanan berhasil dibuat dengan Kode Transaksi: <strong>$kode_transaksi</strong>. Tim kami akan segera memproses cucian Anda!";
                header('Location: riwayat.php');
                exit;
            } catch (Exception $e) {
                $error = 'Gagal menyimpan pesanan: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Laundry - Bagas Laundry Express</title>
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
                <a href="pesan.php" class="nav-link active">Pesan Laundry</a>
                <a href="riwayat.php" class="nav-link">Riwayat Pesanan</a>
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

    <main class="section" style="max-width: 800px; padding-top: 3rem; padding-bottom: 4rem;">
        <div style="margin-bottom: 2rem; text-align: center;">
            <span class="section-subtitle">Pemesanan Online</span>
            <h1 class="section-title">Formulir Pesan Laundry</h1>
            <p class="section-desc">Isi rincian cucian Anda di bawah ini. Tim kami siap melayani dengan bersih, rapi, dan wangi.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <div style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-md); padding: 2.5rem;">
            <form action="pesan.php" method="POST">
                
                <!-- Data Pemesan -->
                <div style="background: var(--bg-page); padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 1.75rem;">
                    <div style="font-weight: 700; color: var(--secondary); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-id-card" style="color: var(--primary);"></i> Informasi Pemesan
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.9rem;">
                        <div>
                            <span style="color: var(--muted);">Nama:</span> <strong><?= htmlspecialchars($current_user['nama']) ?></strong>
                        </div>
                        <div>
                            <span style="color: var(--muted);">No. WhatsApp:</span> <strong><?= htmlspecialchars($current_user['no_hp']) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Pilihan Paket Layanan -->
                <div class="form-group">
                    <label class="form-label" for="calc-layanan">Pilih Paket Layanan *</label>
                    <select id="calc-layanan" name="layanan_id" class="form-select" required>
                        <?php foreach ($layanan_list as $l): ?>
                            <option value="<?= $l['id'] ?>" 
                                    data-harga="<?= $l['harga'] ?>" 
                                    data-jenis="<?= $l['jenis'] ?>"
                                    <?= ($l['id'] == $selected_layanan_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($l['nama_paket']) ?> — <?= rupiah($l['harga']) ?> / <?= $l['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?> (Estimasi <?= $l['estimasi_hari'] ?> Hari)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Estimasi Berat / Jumlah -->
                <div class="form-group">
                    <label class="form-label" for="calc-jumlah">Estimasi Berat / Jumlah Item (<span id="calc-satuan-label">Kg</span>) *</label>
                    <input type="number" id="calc-jumlah" name="berat_jumlah" class="form-control" value="3" min="1" step="0.5" required>
                    <small style="color: var(--muted); font-size: 0.8rem; margin-top: 4px; display: block;">
                        * Berat pasti akan ditimbang ulang secara akurat saat pakaian diterima oleh petugas kami.
                    </small>
                </div>

                <!-- Box Total Estimasi -->
                <div style="margin: 1.5rem 0; padding: 1.25rem; background: var(--primary-subtle); border-radius: var(--radius-sm); border: 1.5px dashed var(--primary); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 0.85rem; color: var(--primary-dark); font-weight: 600;">Estimasi Total Tagihan:</span>
                        <div style="font-size: 0.8rem; color: var(--muted);">Pembayaran dapat dilakukan tunai / transfer saat cucian diterima</div>
                    </div>
                    <div id="calc-total" style="font-size: 1.8rem; font-weight: 800; color: var(--primary-dark);">
                        Rp 0
                    </div>
                </div>

                <!-- Metode Pengambilan -->
                <div class="form-group">
                    <label class="form-label" for="metode_pengambilan">Metode Serah Terima *</label>
                    <select id="metode_pengambilan" name="metode_pengambilan" class="form-select" required>
                        <option value="antar_jemput">Antar Jemput oleh Kurir Laundry (Depan Pintu Rumah)</option>
                        <option value="drop_off">Drop Off Sendiri ke Outlet Bagas Laundry</option>
                    </select>
                </div>

                <!-- Alamat Penjemputan -->
                <div class="form-group" id="alamat-jemput-group">
                    <label class="form-label" for="alamat_jemput">Alamat Penjemputan Pakaian *</label>
                    <textarea id="alamat_jemput" name="alamat_jemput" class="form-control" rows="3" placeholder="Masukkan alamat lengkap penjemputan beserta patokan rumah..."><?= htmlspecialchars($current_user['alamat'] ?? '') ?></textarea>
                </div>

                <!-- Catatan Tambahan -->
                <div class="form-group">
                    <label class="form-label" for="catatan">Catatan Khusus (Opsional)</label>
                    <textarea id="catatan" name="catatan" class="form-control" rows="2" placeholder="Contoh: Baju sutra jangan disikat keras, wangi lavender, minta setrika licin"></textarea>
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <a href="index.php" class="btn btn-outline" style="flex: 1;">
                        <i class="fa-solid fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="flex: 2;">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Pesanan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-bottom" style="border: none; padding-top: 0;">
            &copy; <?= date('Y') ?> Bagas Laundry Express. Seluruh hak cipta dilindungi.
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
