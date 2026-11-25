<?php
require __DIR__ . '/../config/koneksi.php';
/** @var mysqli $conn */
cekRole('superadmin');

$notif = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_layanan'])) {
    $kat_id = (int)$_POST['kategori_id'];
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_layanan']);
    $harga  = (int)$_POST['harga'];
    $desk   = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    mysqli_query($conn, "INSERT INTO layanan (kategori_id, nama_layanan, deskripsi, harga) VALUES ('$kat_id', '$nama', '$desk', '$harga')");
    $notif = "Layanan baru berhasil ditambahkan!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_harga'])) {
    $id_l  = (int)$_POST['id_layanan'];
    $harga = (int)$_POST['harga_baru'];
    mysqli_query($conn, "UPDATE layanan SET harga = '$harga' WHERE id = '$id_l'");
    $notif = "Harga layanan berhasil diperbarui!";
}

if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM layanan WHERE id = '$id_hapus'");
    header("Location: layanan.php");
    exit;
}

$layanan = mysqli_query($conn, "SELECT l.*, k.nama_kategori FROM layanan l JOIN kategori k ON l.kategori_id = k.id ORDER BY l.id DESC");
$kategori = mysqli_query($conn, "SELECT * FROM kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Layanan - Superadmin UMKM Connect</title>
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
                <li><a href="layanan.php" class="active"><i class="fa-solid fa-layer-group"></i> Kelola Layanan</a></li>
                <li><a href="kelola_admin.php"><i class="fa-solid fa-user-shield"></i> Kelola Admin / Kasir</a></li>
                <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
            </ul>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1>Kelola Katalog & Harga Layanan (Owner)</h1>
        </div>

        <?php if ($notif): ?><div class="alert alert-success"><?= $notif ?></div><?php endif; ?>

        <div class="admin-mid-grid">
            <div class="white-card">
                <h3 style="margin-bottom:12px;">Daftar Jasa UMKM</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Layanan</th>
                            <th>Kategori</th>
                            <th>Update Harga</th>
                            <th>Hapus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($l = mysqli_fetch_assoc($layanan)): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($l['nama_layanan']) ?></strong></td>
                            <td><?= htmlspecialchars($l['nama_kategori']) ?></td>
                            <td>
                                <form method="POST" style="display:flex; gap:6px;">
                                    <input type="hidden" name="id_layanan" value="<?= $l['id'] ?>">
                                    <input type="number" name="harga_baru" value="<?= $l['harga'] ?>" class="form-control" style="width:110px; padding:5px 8px;">
                                    <button type="submit" name="update_harga" class="btn-dark-sm">Simpan</button>
                                </form>
                            </td>
                            <td><a href="layanan.php?hapus=<?= $l['id'] ?>" onclick="return confirm('Hapus layanan ini?')" style="color:#E11D48;font-weight:700;">Hapus</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="white-card">
                <h3 style="margin-bottom:12px;">+ Tambah Layanan Baru</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori_id" class="form-control">
                            <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Layanan</label>
                        <input type="text" name="nama_layanan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="harga" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" name="tambah_layanan" class="btn-primary-full">Simpan Layanan</button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>