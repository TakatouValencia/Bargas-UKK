<?php
require_once '../config/database.php';
require_once '../config/auth.php';

require_admin('../auth/login.php');

$trx_id = (int)($_GET['id'] ?? 0);
if ($trx_id <= 0) {
    header('Location: data_laundry.php');
    exit;
}

$error = '';
$success = '';

// Ambil data transaksi lengkap
$stmt = $pdo->prepare("
    SELECT t.*, u.nama AS nama_pelanggan, u.email AS email_pelanggan, u.no_hp, u.alamat AS alamat_user,
           l.nama_paket, l.harga AS harga_layanan, l.jenis, l.estimasi_hari
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    JOIN layanan l ON t.layanan_id = l.id
    WHERE t.id = ?
");
$stmt->execute([$trx_id]);
$trx = $stmt->fetch();

if (!$trx) {
    $_SESSION['flash_error'] = "Transaksi dengan ID #$trx_id tidak ditemukan.";
    header('Location: data_laundry.php');
    exit;
}

// Proses Update Detail & Status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $berat_jumlah = (float)($_POST['berat_jumlah'] ?? $trx['berat_jumlah']);
    $status_pesanan = $_POST['status_pesanan'] ?? $trx['status_pesanan'];
    $status_pembayaran = $_POST['status_pembayaran'] ?? $trx['status_pembayaran'];
    $catatan = trim($_POST['catatan'] ?? $trx['catatan']);

    // Hitung ulang total jika berat/jumlah diubah
    $total_bayar = (int)round($trx['harga_layanan'] * $berat_jumlah);

    try {
        $sql = "UPDATE transaksi SET berat_jumlah = ?, total_bayar = ?, status_pesanan = ?, status_pembayaran = ?, catatan = ? ";
        $params = [$berat_jumlah, $total_bayar, $status_pesanan, $status_pembayaran, $catatan];

        if ($status_pesanan === 'selesai' && empty($trx['tanggal_selesai'])) {
            $sql .= ", tanggal_selesai = NOW() ";
        }

        $sql .= " WHERE id = ?";
        $params[] = $trx_id;

        $stmt_up = $pdo->prepare($sql);
        $stmt_up->execute($params);

        $success = "Data transaksi berhasil diperbarui!";
        
        // Refresh data
        $stmt->execute([$trx_id]);
        $trx = $stmt->fetch();
    } catch (Exception $e) {
        $error = "Gagal memperbarui: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi <?= htmlspecialchars($trx['kode_transaksi']) ?> - Bagas Laundry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="navbar no-print">
        <div class="nav-container">
            <a href="data_laundry.php" class="brand-logo">
                <div class="brand-icon"><i class="fa-solid fa-soap"></i></div>
                <span>Bagas Laundry <small style="font-size: 0.65rem; background: var(--primary-dark); color: white; padding: 2px 6px; border-radius: 4px;">ADMIN</small></span>
            </a>
            <div class="nav-auth">
                <a href="data_laundry.php" class="btn btn-sm btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Laundry
                </a>
            </div>
        </div>
    </header>

    <main class="section" style="max-width: 900px; padding-top: 2.5rem; padding-bottom: 5rem;">
        
        <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="section-subtitle">Rincian & Kelola</span>
                <h1 class="section-title" style="font-size: 1.8rem; font-family: monospace;">
                    <?= htmlspecialchars($trx['kode_transaksi']) ?>
                </h1>
            </div>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fa-solid fa-print"></i> Cetak Struk / Nota
            </button>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success no-print">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= $success ?></div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger no-print">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <!-- NOTA / STRUK TAMPILAN RESMI (JUGA DIGUNAKAN UNTUK PRINT) -->
        <div style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 2.5rem; margin-bottom: 2rem;">
            
            <div style="border-bottom: 2px solid var(--border); padding-bottom: 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="color: var(--primary-dark); font-size: 1.6rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-soap"></i> Bagas Laundry Express
                    </h2>
                    <p style="color: var(--muted); font-size: 0.85rem; margin-top: 4px;">
                        Jl. Bersih Sejahtera No. 12 • Telp: 0812-3456-7890 • Buka Setiap Hari (07.00 - 21.00)
                    </p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.85rem; color: var(--muted); text-transform: uppercase; font-weight: 700;">Nota Transaksi</div>
                    <div style="font-size: 1.1rem; font-weight: 800; font-family: monospace; color: var(--secondary);"><?= htmlspecialchars($trx['kode_transaksi']) ?></div>
                    <div style="font-size: 0.8rem; color: var(--muted); margin-top: 4px;">Masuk: <?= date('d/m/Y H:i', strtotime($trx['tanggal_masuk'])) ?></div>
                </div>
            </div>

            <!-- Identitas Pelanggan -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; background: var(--bg-page); padding: 1.25rem; border-radius: var(--radius-md);">
                <div>
                    <div style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; color: var(--muted);">Informasi Pelanggan</div>
                    <div style="font-size: 1.05rem; font-weight: 700; color: var(--secondary); margin-top: 4px;"><?= htmlspecialchars($trx['nama_pelanggan']) ?></div>
                    <div style="font-size: 0.88rem; color: var(--muted);"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($trx['no_hp']) ?></div>
                    <div style="font-size: 0.88rem; color: var(--muted);"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($trx['email_pelanggan']) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; text-transform: uppercase; font-weight: 700; color: var(--muted);">Metode & Alamat</div>
                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--secondary); margin-top: 4px;">
                        <?= $trx['metode_pengambilan'] === 'antar_jemput' ? '🚚 Antar Jemput Kurir' : '🏢 Drop Off Outlet' ?>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--muted); margin-top: 2px;">
                        <?= htmlspecialchars($trx['alamat_jemput'] ?: ($trx['alamat_user'] ?: 'Antar ke outlet langsung')) ?>
                    </div>
                </div>
            </div>

            <!-- Rincian Item Cucian -->
            <div class="table-responsive" style="margin-bottom: 1.5rem;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item / Paket Layanan</th>
                            <th style="text-align: right;">Tarif Satuan</th>
                            <th style="text-align: center;">Berat / Qty</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($trx['nama_paket']) ?></strong>
                                <div style="font-size: 0.8rem; color: var(--muted);">Estimasi Pengerjaan: <?= $trx['estimasi_hari'] ?> Hari Kerja</div>
                                <?php if (!empty($trx['catatan'])): ?>
                                    <div style="font-size: 0.8rem; color: #0284c7; margin-top: 4px;">
                                        <strong>Catatan:</strong> <?= htmlspecialchars($trx['catatan']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;"><?= rupiah($trx['harga_layanan']) ?> / <?= $trx['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?></td>
                            <td style="text-align: center;"><strong><?= $trx['berat_jumlah'] ?></strong> <?= $trx['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?></td>
                            <td style="text-align: right;"><strong style="font-size: 1.05rem;"><?= rupiah($trx['total_bayar']) ?></strong></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align: right; font-size: 1rem;">Total Tagihan:</th>
                            <th style="text-align: right; font-size: 1.25rem; color: var(--primary-dark);"><?= rupiah($trx['total_bayar']) ?></th>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align: right;">Status Pembayaran:</th>
                            <th style="text-align: right;"><?= badge_status_bayar($trx['status_pembayaran']) ?></th>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align: right;">Status Pengerjaan:</th>
                            <th style="text-align: right;"><?= badge_status_pesanan($trx['status_pesanan']) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--border); font-size: 0.82rem; color: var(--muted);">
                <div>
                    * Simpan struk ini sebagai bukti pengambilan pakaian yang sah.<br>
                    * Klaim komplain maksimal 1x24 jam setelah cucian diambil.
                </div>
                <div style="text-align: center; min-width: 140px;">
                    <div>Petugas / Kasir,</div>
                    <div style="height: 45px;"></div>
                    <div style="font-weight: 700; color: var(--secondary);">( <?= htmlspecialchars($_SESSION['user_nama']) ?> )</div>
                </div>
            </div>
        </div>

        <!-- FORM UPDATE STATUS & DATA OLEH ADMIN -->
        <div class="no-print" style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); padding: 2rem;">
            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-sliders" style="color: var(--primary);"></i> Perbarui Status & Berat Cucian
            </h3>

            <form action="detail_transaksi.php?id=<?= $trx['id'] ?>" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    
                    <div class="form-group">
                        <label class="form-label" for="berat_jumlah">Berat Aktual / Jumlah (<?= $trx['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?>)</label>
                        <input type="number" id="berat_jumlah" name="berat_jumlah" class="form-control" step="0.1" min="0.5" value="<?= $trx['berat_jumlah'] ?>" required>
                        <small style="color: var(--muted); font-size: 0.78rem;">Total tagihan akan dihitung ulang secara otomatis berdasarkan tarif layanan.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status_pembayaran">Status Pembayaran</label>
                        <select id="status_pembayaran" name="status_pembayaran" class="form-select" required>
                            <option value="belum_bayar" <?= $trx['status_pembayaran'] === 'belum_bayar' ? 'selected' : '' ?>>Belum Lunas / Belum Bayar</option>
                            <option value="lunas" <?= $trx['status_pembayaran'] === 'lunas' ? 'selected' : '' ?>>Lunas</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status_pesanan">Status Pengerjaan Cucian</label>
                    <select id="status_pesanan" name="status_pesanan" class="form-select" required>
                        <option value="menunggu_konfirmasi" <?= $trx['status_pesanan'] === 'menunggu_konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                        <option value="dijemput" <?= $trx['status_pesanan'] === 'dijemput' ? 'selected' : '' ?>>Dijemput Kurir</option>
                        <option value="proses_cuci" <?= $trx['status_pesanan'] === 'proses_cuci' ? 'selected' : '' ?>>Sedang Dalam Proses Cuci & Setrika</option>
                        <option value="siap_diambil" <?= $trx['status_pesanan'] === 'siap_diambil' ? 'selected' : '' ?>>Siap Diambil / Diantar</option>
                        <option value="selesai" <?= $trx['status_pesanan'] === 'selesai' ? 'selected' : '' ?>>Selesai (Sudah Diterima Pelanggan)</option>
                        <option value="dibatalkan" <?= $trx['status_pesanan'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="catatan">Catatan Transaksi</label>
                    <textarea id="catatan" name="catatan" class="form-control" rows="2"><?= htmlspecialchars($trx['catatan'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                    <a href="data_laundry.php" class="btn btn-outline">Kembali</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

    </main>

    <footer class="footer no-print">
        <div class="footer-bottom" style="border: none; padding-top: 0;">
            &copy; <?= date('Y') ?> Bagas Laundry Express. Seluruh hak cipta dilindungi.
        </div>
    </footer>

</body>
</html>
