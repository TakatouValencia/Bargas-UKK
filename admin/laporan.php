<?php
require_once '../config/database.php';
require_once '../config/auth.php';

require_admin('../auth/login.php');

// Inisialisasi Tanggal Filter
$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01'); // Awal bulan
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d'); // Hari ini
$status_bayar = $_GET['status_bayar'] ?? '';

$sql = "
    SELECT t.*, u.nama AS nama_pelanggan, u.no_hp, l.nama_paket, l.jenis 
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    JOIN layanan l ON t.layanan_id = l.id
    WHERE DATE(t.tanggal_masuk) BETWEEN ? AND ?
";
$params = [$tgl_dari, $tgl_sampai];

if (!empty($status_bayar)) {
    $sql .= " AND t.status_pembayaran = ?";
    $params[] = $status_bayar;
}

$sql .= " ORDER BY t.tanggal_masuk DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan_data = $stmt->fetchAll();

// Kalkulasi Ringkasan Laporan
$total_transaksi = count($laporan_data);
$total_pendapatan_lunas = 0;
$total_tagihan_keseluruhan = 0;
$total_berat_kiloan = 0;
$total_item_satuan = 0;

foreach ($laporan_data as $row) {
    $total_tagihan_keseluruhan += $row['total_bayar'];
    if ($row['status_pembayaran'] === 'lunas') {
        $total_pendapatan_lunas += $row['total_bayar'];
    }
    if ($row['jenis'] === 'kiloan') {
        $total_berat_kiloan += $row['berat_jumlah'];
    } else {
        $total_item_satuan += $row['berat_jumlah'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Laundry - Bagas Laundry Express</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- NAVBAR ADMIN -->
    <header class="navbar">
        <div class="nav-container">
            <a href="data_laundry.php" class="brand-logo">
                <div class="brand-icon"><i class="fa-solid fa-soap"></i></div>
                <span>Bagas Laundry <small style="font-size: 0.65rem; background: var(--primary-dark); color: white; padding: 2px 6px; border-radius: 4px;">ADMIN</small></span>
            </a>

            <nav class="nav-menu">
                <a href="data_laundry.php" class="nav-link">
                    <i class="fa-solid fa-table-list"></i> Data Laundry
                </a>
                <a href="laporan.php" class="nav-link active">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Laporan
                </a>
                <a href="../index.php" target="_blank" class="nav-link">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Web
                </a>
            </nav>

            <div class="nav-auth">
                <div class="user-profile-badge">
                    <i class="fa-solid fa-user-shield"></i>
                    <span><?= htmlspecialchars($_SESSION['user_nama']) ?></span>
                </div>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline" title="Keluar">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                </a>
            </div>
        </div>
    </header>

    <main class="section" style="padding-top: 2.5rem; padding-bottom: 5rem;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="section-subtitle">Rekapitulasi Keuangan & Transaksi</span>
                <h1 class="section-title">Laporan Transaksi Laundry</h1>
                <p style="color: var(--muted); font-size: 0.95rem;">Filter laporan berdasarkan periode tanggal masuk pesanan dan cetak laporan resmi.</p>
            </div>
            <div>
                <a href="cetak_laporan.php?tgl_dari=<?= urlencode($tgl_dari) ?>&tgl_sampai=<?= urlencode($tgl_sampai) ?>&status_bayar=<?= urlencode($status_bayar) ?>" target="_blank" class="btn btn-primary">
                    <i class="fa-solid fa-print"></i> Cetak Laporan (PDF / Print)
                </a>
            </div>
        </div>

        <!-- Filter Periode Form -->
        <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 2rem;">
            <form action="laporan.php" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto; gap: 1rem; align-items: flex-end;">
                <div>
                    <label class="form-label" style="font-size: 0.82rem;">Dari Tanggal</label>
                    <input type="date" name="tgl_dari" class="form-control" value="<?= htmlspecialchars($tgl_dari) ?>" required>
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.82rem;">Sampai Tanggal</label>
                    <input type="date" name="tgl_sampai" class="form-control" value="<?= htmlspecialchars($tgl_sampai) ?>" required>
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.82rem;">Status Pembayaran</label>
                    <select name="status_bayar" class="form-select">
                        <option value="">Semua Pembayaran</option>
                        <option value="lunas" <?= $status_bayar === 'lunas' ? 'selected' : '' ?>>Hanya Lunas</option>
                        <option value="belum_bayar" <?= $status_bayar === 'belum_bayar' ? 'selected' : '' ?>>Hanya Belum Lunas</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="height: 42px; width: 100%;">
                        <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- Ringkasan Periode Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon mint"><i class="fa-solid fa-file-invoice"></i></div>
                <div>
                    <div class="stat-number"><?= $total_transaksi ?></div>
                    <div class="stat-label">Total Transaksi Masuk</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon mint"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                <div>
                    <div class="stat-number" style="font-size: 1.35rem;"><?= rupiah($total_pendapatan_lunas) ?></div>
                    <div class="stat-label">Pendapatan Diterima (Lunas)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div>
                    <div class="stat-number" style="font-size: 1.35rem;"><?= rupiah($total_tagihan_keseluruhan - $total_pendapatan_lunas) ?></div>
                    <div class="stat-label">Piutang (Belum Bayar)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-weight-hanging"></i></div>
                <div>
                    <div class="stat-number"><?= number_format($total_berat_kiloan, 1) ?> <span style="font-size: 1rem;">Kg</span></div>
                    <div class="stat-label">Total Cucian Kiloan (<?= $total_item_satuan ?> Item Satuan)</div>
                </div>
            </div>
        </div>

        <!-- Tabel Rekap Laporan -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Berat/Qty</th>
                        <th>Tagihan</th>
                        <th>Status Cucian</th>
                        <th>Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan_data)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: var(--muted);">
                                Tidak ada transaksi pada periode <strong><?= date('d/m/Y', strtotime($tgl_dari)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($tgl_sampai)) ?></strong>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($laporan_data as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong style="color: var(--primary-dark); font-family: monospace;">
                                        <?= htmlspecialchars($row['kode_transaksi']) ?>
                                    </strong>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['tanggal_masuk'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong>
                                    <div style="font-size: 0.78rem; color: var(--muted);"><?= htmlspecialchars($row['no_hp']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                                <td><?= $row['berat_jumlah'] ?> <?= $row['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?></td>
                                <td><strong><?= rupiah($row['total_bayar']) ?></strong></td>
                                <td><?= badge_status_pesanan($row['status_pesanan']) ?></td>
                                <td><?= badge_status_bayar($row['status_pembayaran']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($laporan_data)): ?>
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="6" style="text-align: right; padding: 1rem;">TOTAL KESELURUHAN:</td>
                            <td style="color: var(--primary-dark); font-size: 1.05rem;"><?= rupiah($total_tagihan_keseluruhan) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

    </main>

    <footer class="footer">
        <div class="footer-bottom" style="border: none; padding-top: 0;">
            &copy; <?= date('Y') ?> Bagas Laundry Express - Modul Laporan
        </div>
    </footer>

</body>
</html>
