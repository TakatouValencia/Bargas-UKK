<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// Proteksi Halaman Admin
require_admin('../auth/login.php');

$error = '';
$success = '';

if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// Proses Hapus Transaksi
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    try {
        $stmt_del = $pdo->prepare("DELETE FROM transaksi WHERE id = ?");
        $stmt_del->execute([$del_id]);
        $_SESSION['flash_success'] = "Data transaksi #$del_id berhasil dihapus.";
        header('Location: data_laundry.php');
        exit;
    } catch (Exception $e) {
        $error = "Gagal menghapus data: " . $e->getMessage();
    }
}

// Proses Update Cepat Status dari Modal / Quick Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $trx_id = (int)($_POST['trx_id'] ?? 0);
    $new_status = $_POST['status_pesanan'] ?? '';
    $new_bayar = $_POST['status_pembayaran'] ?? '';

    try {
        $sql_update = "UPDATE transaksi SET status_pesanan = ?, status_pembayaran = ? ";
        $params = [$new_status, $new_bayar];

        if ($new_status === 'selesai') {
            $sql_update .= ", tanggal_selesai = IFNULL(tanggal_selesai, NOW()) ";
        }

        $sql_update .= " WHERE id = ?";
        $params[] = $trx_id;

        $stmt_up = $pdo->prepare($sql_update);
        $stmt_up->execute($params);

        $_SESSION['flash_success'] = "Status transaksi #$trx_id berhasil diperbarui.";
        header('Location: data_laundry.php');
        exit;
    } catch (Exception $e) {
        $error = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// Filter Pencarian & Status
$status_filter = $_GET['status'] ?? '';
$search_query = trim($_GET['q'] ?? '');

$sql = "
    SELECT t.*, u.nama AS nama_pelanggan, u.no_hp, l.nama_paket, l.jenis 
    FROM transaksi t
    JOIN users u ON t.user_id = u.id
    JOIN layanan l ON t.layanan_id = l.id
    WHERE 1=1
";
$params = [];

if (!empty($status_filter)) {
    $sql .= " AND t.status_pesanan = ?";
    $params[] = $status_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (t.kode_transaksi LIKE ? OR u.nama LIKE ? OR u.no_hp LIKE ?)";
    $term = "%$search_query%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY t.id DESC";

$stmt_trx = $pdo->prepare($sql);
$stmt_trx->execute($params);
$transaksi_list = $stmt_trx->fetchAll();

// Hitung Statistik Dashboard
$total_trx = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();
$total_proses = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status_pesanan IN ('menunggu_konfirmasi', 'dijemput', 'proses_cuci')")->fetchColumn();
$total_selesai = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status_pesanan = 'selesai'")->fetchColumn();
$total_omset = $pdo->query("SELECT SUM(total_bayar) FROM transaksi WHERE status_pembayaran = 'lunas'")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Laundry - Admin Bagas Laundry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- NAVBAR ADMIN -->
    <header class="navbar">
        <div class="nav-container">
            <a href="data_laundry.php" class="brand-logo">
                <div class="brand-icon">
                    <i class="fa-solid fa-soap"></i>
                </div>
                <span>Bagas Laundry <small style="font-size: 0.65rem; background: var(--primary-dark); color: white; padding: 2px 6px; border-radius: 4px; vertical-align: middle;">ADMIN</small></span>
            </a>

            <nav class="nav-menu">
                <a href="data_laundry.php" class="nav-link active">
                    <i class="fa-solid fa-table-list"></i> Data Laundry
                </a>
                <a href="laporan.php" class="nav-link">
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
        
        <!-- Header Page -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="section-subtitle">Panel Pengelolaan</span>
                <h1 class="section-title">Data Transaksi Laundry</h1>
                <p style="color: var(--muted); font-size: 0.95rem;">Kelola pesanan masuk, pantau status pengerjaan, dan konfirmasi pembayaran.</p>
            </div>
            <a href="laporan.php" class="btn btn-outline-primary">
                <i class="fa-solid fa-print"></i> Rekap & Cetak Laporan
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= $success ?></div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon mint"><i class="fa-solid fa-receipt"></i></div>
                <div>
                    <div class="stat-number"><?= $total_trx ?></div>
                    <div class="stat-label">Total Pesanan Masuk</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-spinner"></i></div>
                <div>
                    <div class="stat-number"><?= $total_proses ?></div>
                    <div class="stat-label">Sedang Dikerjakan</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon mint"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="stat-number"><?= $total_selesai ?></div>
                    <div class="stat-label">Pesanan Selesai</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fa-solid fa-wallet"></i></div>
                <div>
                    <div class="stat-number" style="font-size: 1.3rem;"><?= rupiah($total_omset) ?></div>
                    <div class="stat-label">Pendapatan Lunas</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div style="background: white; padding: 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border); margin-bottom: 1.5rem;">
            <form action="data_laundry.php" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 2; min-width: 240px;">
                    <input type="text" name="q" class="form-control" placeholder="Cari Kode Transaksi, Nama Pelanggan, atau No. HP..." value="<?= htmlspecialchars($search_query) ?>">
                </div>

                <div style="flex: 1; min-width: 180px;">
                    <select name="status" class="form-select">
                        <option value="">Semua Status Pesanan</option>
                        <option value="menunggu_konfirmasi" <?= $status_filter === 'menunggu_konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                        <option value="dijemput" <?= $status_filter === 'dijemput' ? 'selected' : '' ?>>Dijemput</option>
                        <option value="proses_cuci" <?= $status_filter === 'proses_cuci' ? 'selected' : '' ?>>Proses Cuci</option>
                        <option value="siap_diambil" <?= $status_filter === 'siap_diambil' ? 'selected' : '' ?>>Siap Diambil</option>
                        <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="dibatalkan" <?= $status_filter === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>

                <?php if (!empty($status_filter) || !empty($search_query)): ?>
                    <a href="data_laundry.php" class="btn btn-outline">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabel Transaksi -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Berat/Qty</th>
                        <th>Total</th>
                        <th>Status Cucian</th>
                        <th>Bayar</th>
                        <th>Metode</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi_list)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: var(--muted);">
                                <i class="fa-solid fa-inbox" style="font-size: 2.5rem; margin-bottom: 0.75rem; display: block; color: #cbd5e1;"></i>
                                Tidak ditemukan data transaksi yang sesuai.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transaksi_list as $row): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary-dark); font-family: monospace;">
                                        <?= htmlspecialchars($row['kode_transaksi']) ?>
                                    </strong>
                                    <div style="font-size: 0.75rem; color: var(--muted);">
                                        <?= date('d/m/Y H:i', strtotime($row['tanggal_masuk'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color: var(--secondary);"><?= htmlspecialchars($row['nama_pelanggan']) ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--muted);">
                                        <i class="fa-brands fa-whatsapp"></i> <?= htmlspecialchars($row['no_hp']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                                <td><?= $row['berat_jumlah'] ?> <?= $row['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?></td>
                                <td><strong style="color: var(--secondary);"><?= rupiah($row['total_bayar']) ?></strong></td>
                                <td><?= badge_status_pesanan($row['status_pesanan']) ?></td>
                                <td><?= badge_status_bayar($row['status_pembayaran']) ?></td>
                                <td>
                                    <?php if ($row['metode_pengambilan'] === 'antar_jemput'): ?>
                                        <span style="font-size: 0.8rem; color: var(--primary-dark); font-weight: 600;">
                                            <i class="fa-solid fa-truck"></i> Antar Jemput
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size: 0.8rem; color: var(--muted);">Drop Off</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="detail_transaksi.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary" title="Kelola & Detail">
                                        <i class="fa-solid fa-pen-to-square"></i> Kelola
                                    </a>
                                    <a href="data_laundry.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline btn-confirm-delete" style="color: var(--danger);" title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

    <footer class="footer">
        <div class="footer-bottom" style="border: none; padding-top: 0;">
            &copy; <?= date('Y') ?> Bagas Laundry Express - Panel Administrator
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
