<?php
require_once '../config/database.php';
require_once '../config/auth.php';

require_admin('../auth/login.php');

$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
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

$sql .= " ORDER BY t.tanggal_masuk ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan_data = $stmt->fetchAll();

$total_transaksi = count($laporan_data);
$total_omset = 0;
$total_berat = 0;
foreach ($laporan_data as $r) {
    $total_omset += $r['total_bayar'];
    if ($r['jenis'] === 'kiloan') {
        $total_berat += $r['berat_jumlah'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Transaksi Laundry (<?= date('d-m-Y', strtotime($tgl_dari)) ?> s/d <?= date('d-m-Y', strtotime($tgl_sampai)) ?>)</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #fff;
            margin: 0;
            padding: 20px;
            font-size: 11pt;
            line-height: 1.4;
        }
        .header-kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-kop h1 {
            margin: 0;
            font-size: 20pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #065f46;
        }
        .header-kop p {
            margin: 4px 0 0;
            font-size: 9.5pt;
            color: #333;
        }
        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title h2 {
            margin: 0 0 5px;
            font-size: 13pt;
            text-decoration: underline;
        }
        .report-title p {
            margin: 0;
            font-size: 10pt;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        table th, table td {
            border: 1px solid #444;
            padding: 7px 9px;
            text-align: left;
        }
        table th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 10pt;
        }
        .signature-area {
            margin-top: 40px;
            float: right;
            text-align: center;
            width: 220px;
        }
        .signature-area .date {
            margin-bottom: 60px;
        }
        .btn-print-box {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        @media print {
            .btn-print-box {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header-kop">
        <h1>Bagas Laundry Express</h1>
        <p>Layanan Binatu & Cuci Higienis Terpercaya • Bergaransi Bersih & Wangi</p>
        <p>Jl. Bersih Sejahtera No. 12 • Telp/WhatsApp: 0812-3456-7890 • Email: info@bagaslaundry.com</p>
    </div>

    <div class="report-title">
        <h2>LAPORAN TRANSAKSI PENDAPATAN LAUNDRY</h2>
        <p>Periode: <strong><?= date('d F Y', strtotime($tgl_dari)) ?></strong> s/d <strong><?= date('d F Y', strtotime($tgl_sampai)) ?></strong></p>
        <?php if (!empty($status_bayar)): ?>
            <p>Filter Pembayaran: <strong><?= strtoupper($status_bayar) ?></strong></p>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">No</th>
                <th>Kode Transaksi</th>
                <th>Tgl Masuk</th>
                <th>Pelanggan</th>
                <th>Paket Layanan</th>
                <th class="text-center">Berat/Qty</th>
                <th>Status Cuci</th>
                <th>Status Bayar</th>
                <th class="text-right">Total Tagihan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($laporan_data)): ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada transaksi pada periode yang dipilih.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($laporan_data as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($row['kode_transaksi']) ?></strong></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_masuk'])) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                        <td class="text-center"><?= $row['berat_jumlah'] ?> <?= $row['jenis'] === 'kiloan' ? 'Kg' : 'Item' ?></td>
                        <td style="text-transform: capitalize;"><?= str_replace('_', ' ', $row['status_pesanan']) ?></td>
                        <td style="text-transform: capitalize; font-weight: bold;"><?= $row['status_pembayaran'] ?></td>
                        <td class="text-right"><?= rupiah($row['total_bayar']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($laporan_data)): ?>
            <tfoot>
                <tr style="background-color: #f8fafc; font-weight: bold;">
                    <td colspan="5" class="text-right">TOTAL KESELURUHAN:</td>
                    <td class="text-center"><?= number_format($total_berat, 1) ?> Kg</td>
                    <td colspan="2"></td>
                    <td class="text-right" style="font-size: 11pt;"><?= rupiah($total_omset) ?></td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <div style="display: flex; justify-content: space-between;">
        <div style="font-size: 9.5pt; color: #555;">
            Dicetak otomatis oleh Sistem Bagas Laundry Express<br>
            Waktu Cetak: <?= date('d/m/Y H:i:s') ?> WIB
        </div>

        <div class="signature-area">
            <div class="date">
                Kota Anda, <?= date('d F Y') ?><br>
                Penanggung Jawab / Admin,
            </div>
            <div style="font-weight: bold; text-decoration: underline;">
                <?= htmlspecialchars($_SESSION['user_nama']) ?>
            </div>
            <div style="font-size: 9pt; color: #555;">Bagas Laundry Express</div>
        </div>
    </div>

    <!-- Tombol Cetak Manual jika auto-print ditutup -->
    <a href="javascript:window.print()" class="btn-print-box">
        🖨️ Cetak / Simpan PDF
    </a>

</body>
</html>
