<?php
require __DIR__ . '/../config/koneksi.php';
/** @var mysqli $conn */
cekRole('superadmin');

$trx = mysqli_query($conn, "SELECT t.*, p.nama AS nama_pelanggan, p.no_wa, u.nama AS nama_kasir 
                            FROM transaksi t 
                            JOIN pelanggan p ON t.pelanggan_id = p.id 
                            JOIN users u ON t.kasir_id = u.id 
                            ORDER BY t.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi - Owner UMKM Connect</title>
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
                <li><a href="transaksi.php" class="active"><i class="fa-solid fa-file-invoice-dollar"></i> Laporan Transaksi</a></li>
                <li><a href="pelanggan.php"><i class="fa-solid fa-users"></i> Data Member</a></li>
                <li><a href="layanan.php"><i class="fa-solid fa-layer-group"></i> Kelola Layanan</a></li>
                <li><a href="kelola_admin.php"><i class="fa-solid fa-user-shield"></i> Kelola Admin / Kasir</a></li>
                <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
            </ul>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1>Laporan Seluruh Transaksi Layanan</h1>
        </div>
        <div class="white-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Nota</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Kasir Melayani</th>
                        <th>Diskon Poin</th>
                        <th>Total Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($t = mysqli_fetch_assoc($trx)): ?>
                    <tr>
                        <td><strong><?= $t['kode_nota'] ?></strong></td>
                        <td><?= date('d M Y H:i', strtotime($t['created_at'])) ?></td>
                        <td><?= htmlspecialchars($t['nama_pelanggan']) ?></td>
                        <td><span class="badge-tier tier-Silver"><?= htmlspecialchars($t['nama_kasir']) ?></span></td>
                        <td style="color:#0EA65B;">-<?= formatRupiah($t['diskon_poin']) ?></td>
                        <td><strong><?= formatRupiah($t['total_bayar']) ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>