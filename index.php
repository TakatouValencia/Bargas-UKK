<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Ambil data layanan dari database
$layanan_list = [];
try {
    $stmt = $pdo->query("SELECT * FROM layanan ORDER BY id ASC");
    $layanan_list = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback jika database belum diimport
    $layanan_list = [
        ['id' => 1, 'nama_paket' => 'Cuci Komplit Express', 'jenis' => 'kiloan', 'harga' => 10000, 'estimasi_hari' => 1, 'deskripsi' => 'Cuci bersih, pengeringan higienis, setrika rapi, wangi tahan lama (Selesai 24 Jam)', 'icon' => 'fa-bolt'],
        ['id' => 2, 'nama_paket' => 'Cuci Komplit Reguler', 'jenis' => 'kiloan', 'harga' => 7000, 'estimasi_hari' => 2, 'deskripsi' => 'Cuci wangi, kering 100%, setrika rapi & packing plastik segel (2-3 hari)', 'icon' => 'fa-shirt'],
        ['id' => 3, 'nama_paket' => 'Cuci Kering Lipat', 'jenis' => 'kiloan', 'harga' => 5000, 'estimasi_hari' => 2, 'deskripsi' => 'Cuci bersih deterjen khusus, kering wangi & dilipat rapi tanpa setrika', 'icon' => 'fa-wind'],
        ['id' => 4, 'nama_paket' => 'Setrika Uap Saja', 'jenis' => 'kiloan', 'harga' => 4000, 'estimasi_hari' => 1, 'deskripsi' => 'Pakaian rapi licin bebas kusut dengan teknologi setrika uap modern', 'icon' => 'fa-fire'],
        ['id' => 5, 'nama_paket' => 'Bed Cover & Selimut', 'jenis' => 'satuan', 'harga' => 25000, 'estimasi_hari' => 2, 'deskripsi' => 'Pembersihan mendalam untuk bed cover & selimut tebal bebas tungau', 'icon' => 'fa-bed'],
        ['id' => 6, 'nama_paket' => 'Sepatu & Tas Premium', 'jenis' => 'satuan', 'harga' => 35000, 'estimasi_hari' => 3, 'deskripsi' => 'Deep clean sepatu & tas dengan cairan khusus aman semua material', 'icon' => 'fa-shoe-prints']
    ];
}

// Cek jika ada pencarian kode transaksi di landing page
$search_result = null;
$search_error = null;
if (isset($_GET['cari_kode']) && !empty(trim($_GET['cari_kode']))) {
    $kode = trim($_GET['cari_kode']);
    try {
        $stmt_search = $pdo->prepare("
            SELECT t.*, l.nama_paket, u.nama AS nama_pelanggan 
            FROM transaksi t 
            JOIN layanan l ON t.layanan_id = l.id 
            JOIN users u ON t.user_id = u.id 
            WHERE t.kode_transaksi = ?
        ");
        $stmt_search->execute([$kode]);
        $search_result = $stmt_search->fetch();
        if (!$search_result) {
            $search_error = "Pesanan dengan kode \"$kode\" tidak ditemukan. Silakan periksa kembali kode Anda.";
        }
    } catch (Exception $e) {
        $search_error = "Terjadi kesalahan saat melacak pesanan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bagas Laundry Express - Solusi Cuci Pakaian Bersih, Cepat, & Wangi</title>
    <meta name="description" content="Layanan jasa laundry kiloan dan satuan terbaik dengan antar jemput, estimasi harga transparan, dan pengerjaan express.">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
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
                <a href="#beranda" class="nav-link active">Beranda</a>
                <a href="#layanan" class="nav-link">Layanan</a>
                <a href="#kalkulator" class="nav-link">Hitung Biaya</a>
                <a href="#lacak" class="nav-link">Lacak Pesanan</a>
                <a href="#tentang" class="nav-link">Keunggulan</a>
            </nav>

            <div class="nav-auth">
                <?php if (is_logged_in()): ?>
                    <?php if (is_admin()): ?>
                        <a href="admin/data_laundry.php" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-gauge"></i> Panel Admin
                        </a>
                    <?php else: ?>
                        <a href="pesan.php" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-plus-circle"></i> Pesan Cuci
                        </a>
                        <a href="riwayat.php" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                        </a>
                    <?php endif; ?>
                    <a href="auth/logout.php" class="btn btn-sm btn-outline" title="Keluar">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk
                    </a>
                    <a href="auth/register.php" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-user-plus"></i> Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero" id="beranda">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-tag">
                    <i class="fa-solid fa-sparkles"></i> Laundry Modern No. 1 di Kota Anda
                </div>
                <h1 class="hero-title">
                    Pakaian Bersih, Rapi & Wangi <span>Tahan Hingga 7 Hari</span>
                </h1>
                <p class="hero-desc">
                    Nikmati kemudahan mencuci tanpa ribet. Layanan antar-jemput gratis, proses higienis satu mesin per pelanggan, dan deterjen premium ramah serat kain.
                </p>
                <div class="hero-cta-group">
                    <a href="pesan.php" class="btn btn-lg btn-primary">
                        <i class="fa-solid fa-cart-shopping"></i> Pesan Laundry Sekarang
                    </a>
                    <a href="#kalkulator" class="btn btn-lg btn-outline">
                        <i class="fa-solid fa-calculator"></i> Estimasi Biaya
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="stat-item">
                        <h4>10.000+</h4>
                        <p>Kg Pakaian Bersih</p>
                    </div>
                    <div class="stat-item">
                        <h4>24 Jam</h4>
                        <p>Layanan Express</p>
                    </div>
                    <div class="stat-item">
                        <h4>100%</h4>
                        <p>Garansi Bersih & Wangi</p>
                    </div>
                </div>
            </div>

            <!-- Hero Interactive Tracking Form -->
            <div class="hero-card" id="lacak">
                <div style="margin-bottom: 1.25rem;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px;">Status Real-time</span>
                    <h3 style="font-size: 1.35rem; font-weight: 800; margin-top: 4px;">Lacak Cucian Anda</h3>
                    <p style="font-size: 0.88rem; color: var(--muted); margin-top: 4px;">Masukkan kode transaksi yang Anda terima untuk melihat proses cucian.</p>
                </div>

                <form action="index.php#lacak" method="GET">
                    <div class="form-group">
                        <div style="position: relative;">
                            <input type="text" name="cari_kode" class="form-control" placeholder="Contoh: TRX-20260920-001" value="<?= htmlspecialchars($_GET['cari_kode'] ?? '') ?>" required style="padding-left: 2.75rem;">
                            <i class="fa-solid fa-barcode" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--muted);"></i>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-magnifying-glass"></i> Cek Status Sekarang
                    </button>
                </form>

                <?php if ($search_result): ?>
                    <div style="margin-top: 1.5rem; padding: 1.25rem; background: var(--bg-page); border-radius: var(--radius-md); border: 1.5px solid #bfdbfe;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <strong style="font-size: 0.95rem;"><?= htmlspecialchars($search_result['kode_transaksi']) ?></strong>
                            <?= badge_status_pesanan($search_result['status_pesanan']) ?>
                        </div>
                        <div style="font-size: 0.86rem; color: #475569; line-height: 1.6;">
                            <div><strong>Pelanggan:</strong> <?= htmlspecialchars($search_result['nama_pelanggan']) ?></div>
                            <div><strong>Layanan:</strong> <?= htmlspecialchars($search_result['nama_paket']) ?> (<?= $search_result['berat_jumlah'] ?> Kg/Item)</div>
                            <div><strong>Total Bayar:</strong> <?= rupiah($search_result['total_bayar']) ?> (<?= badge_status_bayar($search_result['status_pembayaran']) ?>)</div>
                            <div><strong>Masuk:</strong> <?= tgl_indo($search_result['tanggal_masuk']) ?></div>
                        </div>
                    </div>
                <?php elseif ($search_error): ?>
                    <div class="alert alert-danger" style="margin-top: 1.25rem;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div><?= htmlspecialchars($search_error) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- SECTION: DAFTAR LAYANAN -->
    <section class="section" id="layanan">
        <div class="section-header">
            <span class="section-subtitle">Pilihan Paket Cuci</span>
            <h2 class="section-title">Layanan Terbaik Untuk Pakaian Anda</h2>
            <p class="section-desc">Pilihan layanan yang fleksibel sesuai kebutuhan Anda, mulai dari kiloan hemat hingga perawatan khusus satuan premium.</p>
        </div>

        <div class="grid-3">
            <?php foreach ($layanan_list as $l): ?>
                <div class="card-service">
                    <div class="service-icon-box">
                        <i class="fa-solid <?= htmlspecialchars($l['icon'] ?? 'fa-tshirt') ?>"></i>
                    </div>
                    <h3 class="service-name"><?= htmlspecialchars($l['nama_paket']) ?></h3>
                    <div class="service-price">
                        <?= rupiah($l['harga']) ?> <span>/ <?= $l['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?></span>
                    </div>
                    <p class="service-desc"><?= htmlspecialchars($l['deskripsi']) ?></p>
                    <div style="font-size: 0.85rem; color: var(--muted); margin-bottom: 1.25rem;">
                        <i class="fa-regular fa-clock" style="color: var(--primary);"></i> Estimasi pengerjaan: <strong><?= $l['estimasi_hari'] ?> Hari</strong>
                    </div>
                    <a href="pesan.php?layanan=<?= $l['id'] ?>" class="btn btn-outline-primary btn-block">
                        <i class="fa-solid fa-check"></i> Pilih Layanan
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- SECTION: KALKULATOR ESTIMASI HARGA -->
    <section class="section" id="kalkulator" style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); margin-bottom: 4rem;">
        <div class="grid-2" style="align-items: center;">
            <div>
                <span class="section-subtitle">Transparansi Biaya</span>
                <h2 class="section-title" style="margin-bottom: 1rem;">Kalkulator Estimasi Biaya Laundry</h2>
                <p class="section-desc" style="margin-bottom: 1.5rem;">
                    Hitung perkiraan biaya cucian Anda sebelum memesan. Tidak ada biaya tersembunyi, semua transparan dan terjangkau.
                </p>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 0.95rem;">
                        <i class="fa-solid fa-circle-check" style="color: var(--primary);"></i>
                        <span>Gratis penjemputan untuk pemesanan minimal 5 Kg</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 0.95rem;">
                        <i class="fa-solid fa-circle-check" style="color: var(--primary);"></i>
                        <span>Deterjen anti-bakteri dan pelembut premium</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 0.95rem;">
                        <i class="fa-solid fa-circle-check" style="color: var(--primary);"></i>
                        <span>Pilihan parfum wangi segar yang elegan</span>
                    </div>
                </div>
            </div>

            <div style="background: var(--bg-page); padding: 2rem; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <div class="form-group">
                    <label class="form-label">Pilih Paket Layanan</label>
                    <select id="calc-layanan" class="form-select">
                        <?php foreach ($layanan_list as $l): ?>
                            <option value="<?= $l['id'] ?>" data-harga="<?= $l['harga'] ?>" data-jenis="<?= $l['jenis'] ?>">
                                <?= htmlspecialchars($l['nama_paket']) ?> (<?= rupiah($l['harga']) ?> / <?= $l['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Perkiraan Berat / Jumlah (<span id="calc-satuan-label">Kg</span>)</label>
                    <input type="number" id="calc-jumlah" class="form-control" value="3" min="1" step="0.5">
                </div>

                <div style="margin: 1.75rem 0; padding: 1.25rem; background: var(--primary-subtle); border-radius: var(--radius-sm); border: 1px dashed var(--primary); text-align: center;">
                    <div style="font-size: 0.88rem; color: var(--primary-dark); font-weight: 600;">Estimasi Total Pembayaran:</div>
                    <div id="calc-total" style="font-size: 2.2rem; font-weight: 800; color: var(--primary-dark); margin-top: 4px;">Rp 30.000</div>
                </div>

                <a href="pesan.php" class="btn btn-primary btn-block btn-lg">
                    <i class="fa-solid fa-paper-plane"></i> Lanjutkan Pemesanan
                </a>
            </div>
        </div>
    </section>

    <!-- SECTION: KEUNGGULAN KAMI -->
    <section class="section" id="tentang">
        <div class="section-header">
            <span class="section-subtitle">Mengapa Memilih Kami</span>
            <h2 class="section-title">Standar Mutu Kebersihan Terbaik</h2>
            <p class="section-desc">Kami memperlakukan pakaian Anda dengan standar kebersihan tertinggi untuk menjaga keawetan dan kesegaran serat pakaian.</p>
        </div>

        <div class="grid-4">
            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">1 Mesin 1 Pelanggan</h4>
                <p style="font-size: 0.88rem; color: var(--muted);">Pakaian Anda tidak akan pernah dicampur dengan pakaian pelanggan lain untuk menjaga higienitas.</p>
            </div>

            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Antar Jemput Tepat Waktu</h4>
                <p style="font-size: 0.88rem; color: var(--muted);">Kurir kami siap menjemput dan mengantar pakaian cucian langsung ke depan pintu rumah Anda.</p>
            </div>

            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-spray-can-sparkles"></i></div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Parfum Mewah Tahan Lama</h4>
                <p style="font-size: 0.88rem; color: var(--muted);">Formula wangi eksklusif micro-capsule yang tahan berhari-hari saat disimpan di lemari.</p>
            </div>

            <div class="feature-box">
                <div class="feature-icon"><i class="fa-solid fa-temperature-arrow-up"></i></div>
                <h4 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Setrika Uap Presisi</h4>
                <p style="font-size: 0.88rem; color: var(--muted);">Menghilangkan kerutan tanpa membuat kain mengkilap atau merusak serat pakaian halus.</p>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <h3><i class="fa-solid fa-soap"></i> Bagas Laundry Express</h3>
                <p>
                    Layanan binatu modern profesional dengan teknologi ramah lingkungan, wangi eksklusif, dan jaminan cucian bersih higienis tepat waktu.
                </p>
                <div style="margin-top: 1.25rem; display: flex; gap: 12px;">
                    <span style="display: inline-flex; width: 36px; height: 36px; background: rgba(255,255,255,0.1); border-radius: 50%; align-items: center; justify-content: center; color: white;"><i class="fa-brands fa-whatsapp"></i></span>
                    <span style="display: inline-flex; width: 36px; height: 36px; background: rgba(255,255,255,0.1); border-radius: 50%; align-items: center; justify-content: center; color: white;"><i class="fa-brands fa-instagram"></i></span>
                    <span style="display: inline-flex; width: 36px; height: 36px; background: rgba(255,255,255,0.1); border-radius: 50%; align-items: center; justify-content: center; color: white;"><i class="fa-brands fa-facebook-f"></i></span>
                </div>
            </div>

            <div class="footer-col">
                <h4>Navigasi</h4>
                <ul class="footer-links">
                    <li><a href="#beranda">Beranda</a></li>
                    <li><a href="#layanan">Layanan</a></li>
                    <li><a href="#kalkulator">Hitung Biaya</a></li>
                    <li><a href="#lacak">Lacak Cucian</a></li>
                    <li><a href="pesan.php">Pesan Online</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Paket Laundry</h4>
                <ul class="footer-links">
                    <li><a href="#layanan">Cuci Komplit Express</a></li>
                    <li><a href="#layanan">Cuci Komplit Reguler</a></li>
                    <li><a href="#layanan">Cuci Kering Lipat</a></li>
                    <li><a href="#layanan">Setrika Uap Saja</a></li>
                    <li><a href="#layanan">Bed Cover & Sepatu</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Jam Buka & Kontak</h4>
                <div style="font-size: 0.88rem; color: #94a3b8; line-height: 1.7;">
                    <p><i class="fa-solid fa-clock" style="color: var(--primary-light); margin-right: 6px;"></i> Setiap Hari: 07.00 - 21.00 WIB</p>
                    <p style="margin-top: 0.5rem;"><i class="fa-solid fa-location-dot" style="color: var(--primary-light); margin-right: 6px;"></i> Jl. Bersih Sejahtera No. 12, Kota Anda</p>
                    <p style="margin-top: 0.5rem;"><i class="fa-solid fa-phone" style="color: var(--primary-light); margin-right: 6px;"></i> 0812-3456-7890</p>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; <?= date('Y') ?> Bagas Laundry Express. Seluruh hak cipta dilindungi undang-undang.
        </div>
    </footer>

    <!-- Custom Script -->
    <script src="assets/js/main.js"></script>
</body>
</html>
