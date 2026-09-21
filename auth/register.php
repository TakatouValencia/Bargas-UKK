<?php
require_once '../config/database.php';
require_once '../config/auth.php';

$error = '';
$success = '';

// Jika sudah login, redirect
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($nama) || empty($email) || empty($no_hp) || empty($password)) {
        $error = 'Harap lengkapi semua field yang berbintang wajib (*).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal harus 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        try {
            // Cek apakah email sudah terdaftar
            $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt_check->execute([$email]);
            if ($stmt_check->fetch()) {
                $error = 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.';
            } else {
                // Enkripsi password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $role = 'pelanggan';

                $stmt_insert = $pdo->prepare("
                    INSERT INTO users (nama, email, password, no_hp, alamat, role, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt_insert->execute([$nama, $email, $hashed_password, $no_hp, $alamat, $role]);

                $user_id = $pdo->lastInsertId();

                // Langsung login otomatis setelah pendaftaran sukses
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_nama'] = $nama;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
                $_SESSION['user_no_hp'] = $no_hp;
                $_SESSION['user_alamat'] = $alamat;
                $_SESSION['flash_success'] = 'Pendaftaran akun berhasil! Selamat datang di Bagas Laundry Express.';

                header('Location: ../pesan.php');
                exit;
            }
        } catch (Exception $e) {
            $error = 'Gagal mendaftarkan akun: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - Bagas Laundry Express</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: linear-gradient(135deg, #eff6ff 0%, #e2e8f0 100%);">

    <div class="auth-wrapper" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="auth-card" style="max-width: 520px;">
            <div class="auth-header">
                <a href="../index.php" class="brand-logo" style="justify-content: center; margin-bottom: 1rem;">
                    <div class="brand-icon">
                        <i class="fa-solid fa-soap"></i>
                    </div>
                    <span>Bagas Laundry</span>
                </a>
                <h2 class="auth-title">Buat Akun Baru</h2>
                <p style="color: var(--muted); font-size: 0.9rem; margin-top: 4px;">Daftar untuk kemudahan pemesanan & pantau status cucian Anda</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="no_hp">Nomor WhatsApp / HP *</label>
                    <input type="tel" id="no_hp" name="no_hp" class="form-control" placeholder="081234567890" required value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="alamat">Alamat Penjemputan Default</label>
                    <textarea id="alamat" name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="password">Kata Sandi *</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirm">Konfirmasi Sandi *</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Ulangi sandi" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1rem;">
                    <i class="fa-solid fa-user-plus"></i> Daftarkan Sekarang
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--muted);">
                Sudah memiliki akun? <a href="login.php" style="color: var(--primary-dark); font-weight: 700;">Masuk di Sini</a>
            </div>

            <div style="text-align: center; margin-top: 1rem;">
                <a href="../index.php" style="font-size: 0.85rem; color: var(--muted);">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </div>

</body>
</html>
