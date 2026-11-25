<?php
require __DIR__ . '/../config/koneksi.php';
/** @var mysqli $conn */
cekRole('superadmin');

$notif = '';

// Proses Update Akun (Superadmin atau 1 Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_akun'])) {
    $id_user = (int)$_POST['id_user'];
    $nama    = mysqli_real_escape_string($conn, $_POST['nama']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $pass    = $_POST['password'];

    $cek = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != '$id_user'");
    if (mysqli_num_rows($cek) > 0) {
        $notif = "<div class='alert alert-danger'>Email sudah dipakai akun lain!</div>";
    } else {
        if (!empty($pass)) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET nama = '$nama', email = '$email', password = '$hash' WHERE id = '$id_user'");
        } else {
            mysqli_query($conn, "UPDATE users SET nama = '$nama', email = '$email' WHERE id = '$id_user'");
        }

        if ($id_user == $_SESSION['user_id']) {
            $_SESSION['nama'] = $nama;
        }
        $notif = "<div class='alert alert-success'>Data akun berhasil diperbarui!</div>";
    }
}

$akun_superadmin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE role = 'superadmin' LIMIT 1"));
$akun_admin      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE role = 'admin' LIMIT 1"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola 2 Akun Sistem - UMKM Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-frame two-col">
    <aside class="sidebar">
        <div>
            <div class="brand-logo">UMKM Connect</div>
            <ul class="nav-menu">
                <li><a href="dashboard.php"><i class="fa-solid fa-crown"></i> Dashboard Owner</a></li>
                <li><a href="transaksi.php"><i class="fa-solid fa-file-invoice-dollar"></i> Laporan Transaksi</a></li>
                <li><a href="pelanggan.php"><i class="fa-solid fa-users"></i> Data Member</a></li>
                <li><a href="layanan.php"><i class="fa-solid fa-layer-group"></i> Kelola Layanan</a></li>
                <li><a href="kelola_admin.php" class="active"><i class="fa-solid fa-user-shield"></i> Pengaturan Akun</a></li>
                <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
            </ul>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1>Pengaturan 2 Akun (Superadmin & Admin)</h1>
        </div>

        <?= $notif ?>

        <div class="admin-mid-grid" style="grid-template-columns: 1fr 1fr;">
            <!-- Form Akun 1: Superadmin (Owner) -->
            <div class="white-card">
                <span class="badge-tier tier-Gold" style="display:inline-block; margin-bottom:10px;">AKUN 1 • SUPERADMIN (OWNER)</span>
                <h3 style="margin-bottom:14px;">Profil & Akses Owner</h3>
                <form method="POST">
                    <input type="hidden" name="id_user" value="<?= $akun_superadmin['id'] ?>">
                    <div class="form-group">
                        <label>Nama Owner</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($akun_superadmin['nama']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Login Owner</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($akun_superadmin['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru <small style="color:#8E8CA7;">(Kosongkan jika tidak diubah)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>
                    <button type="submit" name="update_akun" class="btn-primary-full">Simpan Akun Owner</button>
                </form>
            </div>

            <!-- Form Akun 2: Admin (Kasir Tunggal) -->
            <div class="white-card">
                <span class="badge-tier tier-Silver" style="display:inline-block; margin-bottom:10px;">AKUN 2 • ADMIN (KASIR)</span>
                <h3 style="margin-bottom:14px;">Profil & Akses Admin</h3>
                <form method="POST">
                    <input type="hidden" name="id_user" value="<?= $akun_admin['id'] ?>">
                    <div class="form-group">
                        <label>Nama Admin / Kasir</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($akun_admin['nama']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Login Admin</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($akun_admin['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru <small style="color:#8E8CA7;">(Kosongkan jika tidak diubah)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                    </div>
                    <button type="submit" name="update_akun" class="btn-dark-sm" style="width:100%; padding:13px; margin-top:12px;">Simpan Akun Admin</button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>