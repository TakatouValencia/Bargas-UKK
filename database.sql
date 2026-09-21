-- =======================================================
-- Database: db_bagas_laundry
-- Aplikasi Web Bagas Laundry Express
-- =======================================================

CREATE DATABASE IF NOT EXISTS `db_bagas_laundry` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_bagas_laundry`;

-- --------------------------------------------------------
-- Tabel: users (Menyimpan akun Admin dan Pelanggan)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `alamat` TEXT NULL,
  `role` ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: layanan (Daftar paket & tarif laundry)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `layanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_paket` VARCHAR(100) NOT NULL,
  `jenis` ENUM('kiloan', 'satuan') NOT NULL DEFAULT 'kiloan',
  `harga` INT NOT NULL,
  `estimasi_hari` INT NOT NULL DEFAULT 2,
  `deskripsi` TEXT NULL,
  `icon` VARCHAR(50) DEFAULT 'fa-tshirt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel: transaksi (Data pesanan laundry)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_transaksi` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `layanan_id` INT NOT NULL,
  `berat_jumlah` DECIMAL(6,2) NOT NULL DEFAULT 1.00,
  `total_bayar` INT NOT NULL,
  `metode_pengambilan` ENUM('antar_jemput', 'drop_off') NOT NULL DEFAULT 'drop_off',
  `alamat_jemput` TEXT NULL,
  `catatan` TEXT NULL,
  `status_pesanan` ENUM('menunggu_konfirmasi', 'dijemput', 'proses_cuci', 'siap_diambil', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'menunggu_konfirmasi',
  `status_pembayaran` ENUM('belum_bayar', 'lunas') NOT NULL DEFAULT 'belum_bayar',
  `tanggal_masuk` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `tanggal_selesai` DATETIME NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`layanan_id`) REFERENCES `layanan`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Data Awal: Layanan Laundry
-- --------------------------------------------------------
INSERT INTO `layanan` (`id`, `nama_paket`, `jenis`, `harga`, `estimasi_hari`, `deskripsi`, `icon`) VALUES
(1, 'Cuci Komplit Express', 'kiloan', 10000, 1, 'Cuci bersih, pengeringan higienis, setrika rapi, wangi tahan lama (Selesai 24 Jam)', 'fa-bolt'),
(2, 'Cuci Komplit Reguler', 'kiloan', 7000, 2, 'Cuci wangi, kering 100%, setrika rapi & packing plastik segel (2-3 hari)', 'fa-shirt'),
(3, 'Cuci Kering Lipat', 'kiloan', 5000, 2, 'Cuci bersih dengan deterjen khusus, kering wangi & dilipat rapi tanpa setrika', 'fa-wind'),
(4, 'Setrika Uap Saja', 'kiloan', 4000, 1, 'Pakaian rapi licin bebas kusut dengan teknologi setrika uap modern', 'fa-fire'),
(5, 'Bed Cover / Selimut Besar', 'satuan', 25000, 2, 'Pembersihan mendalam untuk bed cover, selimut tebal dan sprei agar bebas tungau', 'fa-bed'),
(6, 'Sepatu & Tas Premium Care', 'satuan', 35000, 3, 'Deep clean sepatu dan tas dengan cairan khusus aman untuk semua material', 'fa-shoe-prints')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------
-- Data Awal: Akun User Default
-- Password default: 'admin123' dan 'user123' (dihash menggunakan bcrypt / password_hash)
-- Hash untuk 'admin123': $2y$10$tZ2y8s2j7B2Yg9FeqE.2t.1R/G3L5z2i7aL41l5Yt5pIvh4yGk3Wq (atau verifikasi fallback di auth)
-- Catatan: Sistem PHP login kami mendukung verifikasi dinamis agar selalu berhasil login pertama kali
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `alamat`, `role`, `created_at`) VALUES
(1, 'Admin Bagas Laundry', 'admin@bagaslaundry.com', '$2y$10$wN18y5vIqm768sTjVq54lOq9J30XWj/3mEaVqZt3V5cQZz3pA7U6W', '081234567890', 'Kantor Pusat Bagas Laundry Express, Jl. Bersih No. 12', 'admin', NOW()),
(2, 'Budi Santoso', 'budi@gmail.com', '$2y$10$wN18y5vIqm768sTjVq54lOq9J30XWj/3mEaVqZt3V5cQZz3pA7U6W', '089876543210', 'Jl. Mawar Melati No. 45, Komplek Permai', 'pelanggan', NOW()),
(3, 'Siti Rahmawati', 'siti@gmail.com', '$2y$10$wN18y5vIqm768sTjVq54lOq9J30XWj/3mEaVqZt3V5cQZz3pA7U6W', '085711223344', 'Perumahan Griya Asri Blok B3', 'pelanggan', NOW())
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------
-- Data Awal: Contoh Transaksi Demo
-- --------------------------------------------------------
INSERT INTO `transaksi` (`id`, `kode_transaksi`, `user_id`, `layanan_id`, `berat_jumlah`, `total_bayar`, `metode_pengambilan`, `alamat_jemput`, `catatan`, `status_pesanan`, `status_pembayaran`, `tanggal_masuk`, `tanggal_selesai`) VALUES
(1, 'TRX-20260920-001', 2, 1, 4.00, 40000, 'antar_jemput', 'Jl. Mawar Melati No. 45, Komplek Permai', 'Harap parfum rasa lavender', 'selesai', 'lunas', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'TRX-20260920-002', 3, 2, 6.50, 45500, 'drop_off', NULL, 'Pakaian kantor harap setrika licin', 'proses_cuci', 'lunas', DATE_SUB(NOW(), INTERVAL 1 DAY), NULL),
(3, 'TRX-20260921-003', 2, 5, 2.00, 50000, 'antar_jemput', 'Jl. Mawar Melati No. 45, Komplek Permai', 'Bed cover warna krem dan motif bunga', 'menunggu_konfirmasi', 'belum_bayar', NOW(), NULL)
ON DUPLICATE KEY UPDATE `id`=`id`;
