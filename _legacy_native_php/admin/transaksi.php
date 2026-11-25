<?php
require __DIR__ . '/../config/koneksi.php';
/** @var mysqli $conn */
cekRole('admin');

$trx = mysqli_query($conn, "SELECT t.*, p.nama AS nama_pelanggan, p.no_wa, p.poin AS saldo_sekarang 
                            FROM transaksi t 
                            JOIN pelanggan p ON t.pelanggan_id = p.id 
                            ORDER BY t.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat & Nota WA - Kasir UMKM Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-frame two-col">
    <aside class="sidebar">
        <div>
            <div class="brand-logo">UMKM Connect</div>
            <ul class="nav-menu">
                <li><a href="dashboard.php"><i class="fa-solid fa-cash-register"></i> Kasir & Transaksi</a></li>
                <li><a href="pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
                <li><a href="transaksi.php" class="active"><i class="fa-solid fa-receipt"></i> Riwayat & Nota WA</a></li>
                <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
            </ul>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1>Riwayat Transaksi & Kirim Ulang Nota WA</h1>
        </div>
        <div class="white-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Nota</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Total Bayar</th>
                        <th>Poin Didapat</th>
                        <th>Nota Digital</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($t = mysqli_fetch_assoc($trx)): 
                        $pesan_ulang = "Halo *{$t['nama_pelanggan']}*, berikut salinan Nota Digital *UMKM Connect*:\n"
                                     . "No. Nota: *{$t['kode_nota']}*\n"
                                     . "Total Bayar: *" . formatRupiah($t['total_bayar']) . "*\n"
                                     . "Poin Didapat: +{$t['poin_didapat']} Poin\n"
                                     . "Saldo Poin Saat Ini: *{$t['saldo_sekarang']} Poin*";
                        $link_wa = "https://wa.me/{$t['no_wa']}?text=" . urlencode($pesan_ulang);
                    ?>
                    <tr>
                        <td><strong><?= $t['kode_nota'] ?></strong></td>
                        <td><?= date('d M Y H:i', strtotime($t['created_at'])) ?></td>
                        <td><?= htmlspecialchars($t['nama_pelanggan']) ?></td>
                        <td><strong><?= formatRupiah($t['total_bayar']) ?></strong></td>
                        <td style="color:#0EA65B;">+<?= $t['poin_didapat'] ?> Poin</td>
                        <td>
                            <a href="<?= $link_wa ?>" target="_blank" class="btn-dark-sm" style="background:#16A34A;">
                                <i class="fa-brands fa-whatsapp"></i> Kirim WA
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>