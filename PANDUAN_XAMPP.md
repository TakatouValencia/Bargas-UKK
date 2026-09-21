# 📖 Panduan Lengkap Menjalankan Web Bagas Laundry Express di XAMPP

Aplikasi web **Bagas Laundry Express** dibuat menggunakan PHP Native, HTML5, Vanilla CSS3 (Modern Fresh Mint Green), dan database MySQL.

Panduan ini berisi langkah-langkah praktis mulai dari instalasi, konfigurasi database melalui phpMyAdmin, hingga uji coba seluruh fitur.

---

## 📋 Daftar Isi
1. [Prasyarat](#1-prasyarat)
2. [Menyalakan Service XAMPP](#2-menyalakan-service-xampp)
3. [Meletakkan Folder Proyek di XAMPP (htdocs)](#3-meletakkan-folder-proyek-di-xampp-htdocs)
4. [Membuat Database & Impor Data (phpMyAdmin)](#4-membuat-database--impor-data-phpmyadmin)
5. [Konfigurasi Koneksi Database](#5-konfigurasi-koneksi-database)
6. [Membuka Aplikasi di Browser](#6-membuka-aplikasi-di-browser)
7. [Daftar Akun Login Demo](#7-daftar-akun-login-demo)
8. [Panduan Alur Pengujian Fitur](#8-panduan-alur-pengujian-fitur)
9. [Solusi Masalah Umum (Troubleshooting)](#9-solusi-masalah-umum-troubleshooting)

---

## 1. Prasyarat
- Komputer / Laptop dengan OS Windows.
- Aplikasi **XAMPP** sudah terinstal (disarankan XAMPP dengan PHP 7.4, 8.0, 8.1, atau 8.2+).
- Web Browser modern (Google Chrome, Microsoft Edge, atau Mozilla Firefox).

---

## 2. Menyalakan Service XAMPP
1. Buka aplikasi **XAMPP Control Panel** dari menu Start atau cari `xampp-control.exe`.
2. Pada baris **Apache**, klik tombol **Start** hingga indikator berubah warna menjadi **Hijau**.
3. Pada baris **MySQL**, klik tombol **Start** hingga indikator berubah warna menjadi **Hijau**.

> 💡 *Catatan: Pastikan kedua service (Apache & MySQL) berwarna hijau tanpa tulisan merah error.*

---

## 3. Meletakkan Folder Proyek di XAMPP (htdocs)
Agar Apache dapat membaca file web PHP, folder proyek harus berada di dalam direktori `htdocs` milik XAMPP.

1. Buka File Explorer di Windows.
2. Salin (copy) folder proyek ini:
   ```
   UKK Bagas
   ```
3. Tempelkan (paste) ke dalam direktori XAMPP Anda, standarnya berada di:
   ```
   C:\xampp\htdocs\
   ```
4. Sekarang susunan folder Anda menjadi:
   ```
   C:\xampp\htdocs\UKK Bagas\
   ```
   *(Opsional: Anda juga dapat mengganti nama folder menjadi lebih pendek, misalnya `laundry`, sehingga foldernya menjadi `C:\xampp\htdocs\laundry\`)*

---

## 4. Membuat Database & Impor Data (phpMyAdmin)
1. Buka browser Anda (Google Chrome / Edge).
2. Kunjungi alamat berikut:
   ```
   http://localhost/phpmyadmin
   ```
3. Klik menu **"Baru"** atau **"New"** pada panel sebelah kiri.
4. Pada kolom **Nama basis data (Database name)**, ketik:
   ```text
   db_bagas_laundry
   ```
5. Pada pilihan collation biarkan default (`utf8mb4_general_ci` atau sejenisnya), lalu klik tombol **"Buat" / "Create"**.
6. Klik nama database `db_bagas_laundry` yang baru dibuat pada menu sebelah kiri.
7. Di bagian atas layar, klik tab **"Impor" / "Import"**.
8. Pada bagian **Berkas yang diimpor (File to import)**, klik tombol **"Choose File"** (Pilih Berkas).
9. Arahkan ke file:
   ```
   C:\xampp\htdocs\UKK Bagas\database.sql
   ```
10. Gulir ke bagian paling bawah dan klik tombol **"Kirim" / "Import" / "Go"**.
11. Tunggu beberapa detik hingga muncul pesan sukses berwarna hijau:
    *`Impor berhasil diselesaikan, X kueri telah dieksekusi.`*

---

## 5. Konfigurasi Koneksi Database
File konfigurasi database berada di:
`config/database.php`

Secara bawaan, file ini telah disesuaikan dengan konfigurasi standar XAMPP:
- **Host**: `localhost`
- **Username**: `root`
- **Password**: `""` (kosong tanpa spasi)
- **Database**: `db_bagas_laundry`

Jika Anda tidak pernah mengubah password root MySQL XAMPP Anda, maka **tidak perlu ada file yang diubah sama sekali**.

---

## 6. Membuka Aplikasi di Browser
Setelah Apache & MySQL berjalan dan database telah diimpor, buka browser Anda dan akses tautan berikut:

```
http://localhost/UKK%20Bagas/
```
*(Atau jika nama folder diganti menjadi `laundry`, akses `http://localhost/laundry/`)*

Anda akan langsung disambut oleh **Landing Page Modern Bagas Laundry Express**!

---

## 7. Daftar Akun Login Demo

Aplikasi ini dilengkapi 2 jenis peran (*Multi-Role*) dengan data demo siap pakai:

| Role | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@bagaslaundry.com` | `admin123` | Mengelola semua pesanan, update status pengerjaan, konfirmasi bayar, & cetak laporan |
| **Pelanggan** | `budi@gmail.com` | `user123` | Memesan laundry, melihat kalkulasi harga, melacak status cucian di riwayat |

*(Anda juga bisa mendaftarkan akun pelanggan baru kapan saja melalui menu **Daftar** di pojok kanan atas).*

---

## 8. Panduan Alur Pengujian Fitur

### A. Fitur Landing Page
1. Kunjungi `http://localhost/UKK%20Bagas/`.
2. Uji **Kalkulator Biaya**: Ubah paket cucian dan berat (Kg). Perhatikan estimasi biaya langsung terhitung otomatis.
3. Uji **Lacak Cucian Cepat**: Masukkan salah satu kode transaksi demo berikut ke kolom pelacakan:
   - `TRX-20260920-001` (Status: Selesai)
   - `TRX-20260920-002` (Status: Proses Cuci)
   - `TRX-20260921-003` (Status: Menunggu Konfirmasi)

### B. Fitur Pelanggan (Pemesanan & Riwayat)
1. Klik tombol **Masuk** dan login dengan akun pelanggan (`budi@gmail.com` / `user123`).
2. Klik menu **Pesan Laundry**.
3. Pilih jenis paket, tentukan berat cucian, pilih metode serah terima (Antar Jemput / Drop Off), dan masukkan catatan khusus.
4. Klik **Kirim Pesanan Sekarang**.
5. Sistem akan mengarahkan ke halaman **Riwayat Pesanan** dengan status *Menunggu Konfirmasi*.

### C. Fitur Admin (Data Laundry & Kelola Status)
1. Logout dari akun pelanggan, lalu login dengan akun Admin (`admin@bagaslaundry.com` / `admin123`).
2. Anda akan otomatis diarahkan ke halaman **Data Transaksi Laundry**.
3. Di sini admin dapat:
   - Melihat rekap total pesanan, jumlah cucian aktif, dan total omset.
   - Filter pesanan berdasarkan status cuci atau mencari nama pelanggan.
   - Klik tombol **Kelola** pada salah satu pesanan untuk mengubah status menjadi *Proses Cuci*, *Siap Diambil*, atau *Selesai*.
   - Mengubah status pembayaran menjadi *Lunas*.
   - Mencetak nota struk perorangan dengan klik **Cetak Struk / Nota**.

### D. Fitur Laporan & Cetak PDF
1. Pada menu Admin di atas, klik **Laporan**.
2. Pilih rentang tanggal transaksi (*Dari Tanggal* dan *Sampai Tanggal*).
3. Klik **Tampilkan** untuk melihat total omset, tagihan, dan total berat yang dikerjakan pada periode tersebut.
4. Klik tombol **Cetak Laporan (PDF / Print)**. Jendela print browser akan langsung muncul secara otomatis dengan format laporan resmi siap cetak atau disimpan sebagai PDF.

---

## 9. Solusi Masalah Umum (Troubleshooting)

### Q: Muncul pesan error "⚠️ Gagal Terhubung ke Database MySQL"
- **Solusi**: Pastikan tombol MySQL pada XAMPP Control Panel sudah berwarna hijau. Pastikan juga nama database yang Anda buat di phpMyAdmin adalah tepat `db_bagas_laundry`.

### Q: Apache tidak bisa Start (Port 80 bentrok)
- **Solusi**: Biasanya bentrok dengan aplikasi lain seperti Skype atau IIS. Buka XAMPP Control Panel -> klik **Config** pada baris Apache -> pilih `httpd.conf` -> cari `Listen 80` dan ubah menjadi `Listen 8080`. Lalu akses web dengan `http://localhost:8080/UKK%20Bagas/`.

### Q: Login demo tidak berhasil
- **Solusi**: Pastikan file `database.sql` sudah terimpor dengan lengkap. Sistem login juga telah dilengkapi verifikasi toleran untuk kata sandi demo `admin123` dan `user123`.

---
*Dibuat untuk kebutuhan tugas / UKK Web Development Bagas Laundry Express.*
