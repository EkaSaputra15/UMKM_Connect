<?php
require __DIR__ . '/../config/koneksi.php';
/** @var mysqli $conn */
cekRole('superadmin');

$notif = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_member_owner'])) {
    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_wa     = formatNoWa($_POST['no_wa']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $tgl_lahir = mysqli_real_escape_string($conn, $_POST['tgl_lahir']);

    mysqli_query($conn, "INSERT INTO pelanggan (nama, no_wa, email, tgl_lahir, poin, tier, status_aktif) 
                         VALUES ('$nama', '$no_wa', '$email', '$tgl_lahir', 50, 'Bronze', 'Aktif')");
    $notif = "Member baru <strong>$nama</strong> berhasil ditambahkan (+50 Poin).";
}

if (isset($_GET['hapus'])) {
    $id_h = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM pelanggan WHERE id = '$id_h'");
    header("Location: pelanggan.php");
    exit;
}

$pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY poin DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Member - Owner UMKM Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=16">
</head>
<body>

<!-- OBJEK GEOMETRIS 3D MELAYANG DI LATAR -->
<div class="bg-3d-shapes">
    <div class="ring-3d r1"></div>
    <div class="ring-3d r2"></div>
    <div class="sphere-3d gold-s1"></div>
    <div class="sphere-3d purple-s2"></div>
</div>

<div class="app-frame two-col">
    <aside class="sidebar">
        <div>
            <div class="brand-logo">UMKM Connect</div>
            <ul class="nav-menu">
                <li><a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
                <li><a href="transaksi.php"><i class="fa-solid fa-file-invoice-dollar"></i> Transaksi</a></li>
                <li><a href="pelanggan.php" class="active"><i class="fa-solid fa-users"></i> Data Member</a></li>
                <li><a href="layanan.php"><i class="fa-solid fa-layer-group"></i> Layanan</a></li>
                <li><a href="kelola_admin.php"><i class="fa-solid fa-user-shield"></i> Pengaturan Akun</a></li>
                <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
            </ul>
        </div>

        <div class="sidebar-promo-card">
            <span class="badge-tag">OWNER</span>
            <h4><?= htmlspecialchars($_SESSION['nama']) ?></h4>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1>Database Member Pelanggan UMKM</h1>
            <div class="avatar-pill"><i class="fa-solid fa-crown"></i> Kontrol Owner</div>
        </div>

        <?php if ($notif): ?><div class="alert alert-success"><?= $notif ?></div><?php endif; ?>

        <div class="admin-mid-grid" style="grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);">
            <div class="white-card tilt-3d">
                <div class="card-glare"></div>
                <div class="section-header">
                    <h3><i class="fa-solid fa-users"></i> Daftar Member Pelanggan</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Pelanggan</th>
                            <th>No WhatsApp</th>
                            <th>Email</th>
                            <th>Level</th>
                            <th>Saldo Poin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($pelanggan)):
                            $inisial = strtoupper(substr($p['nama'], 0, 1));
                        ?>
                        <tr>
                            <td>
                                <div class="member-cell">
                                    <div class="member-avatar"><?= $inisial ?></div>
                                    <strong><?= htmlspecialchars($p['nama']) ?></strong>
                                </div>
                            </td>
                            <td class="nowrap-cell"><?= htmlspecialchars($p['no_wa']) ?></td>
                            <td><?= htmlspecialchars($p['email'] ?? '-') ?></td>
                            <td><span class="badge-tier tier-<?= $p['tier'] ?>"><?= $p['tier'] ?></span></td>
                            <td class="nowrap-cell"><strong style="color:#5B35E5;"><?= number_format($p['poin'], 0, ',', '.') ?> Poin</strong></td>
                            <td class="nowrap-cell">
                                <a href="pelanggan.php?hapus=<?= $p['id'] ?>" onclick="return confirm('Hapus data member ini?')" style="color:#E11D48;font-weight:700;text-decoration:none;">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="white-card tilt-3d">
                <div class="card-glare"></div>
                <div class="section-header">
                    <h3><i class="fa-solid fa-user-plus"></i> Tambah Member Baru</h3>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Pelanggan</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama lengkap member" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor WhatsApp</label>
                        <input type="text" name="no_wa" class="form-control" placeholder="0812..." required>
                    </div>
                    <div class="form-group">
                        <label>Alamat Email (Untuk Promo)</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" class="form-control" required>
                    </div>
                    <button type="submit" name="tambah_member_owner" class="btn-primary-full">
                        <i class="fa-solid fa-plus"></i> Simpan Data Member
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
// Efek 3D Tilt & Glare Interaktif
document.querySelectorAll('.tilt-3d').forEach(card => {
    const glare = card.querySelector('.card-glare');
    card.addEventListener('mousemove', e => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = ((y - centerY) / centerY) * -4.5;
        const rotateY = ((x - centerX) / centerX) * 4.5;

        card.style.transform = `perspective(1100px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-3px)`;
        if (glare) {
            glare.style.setProperty('--gx', `${(x / rect.width) * 100}%`);
            glare.style.setProperty('--gy', `${(y / rect.height) * 100}%`);
        }
    });
    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
});

// Animasi Tirai Keluar saat Tombol Keluar Ditekan
document.querySelectorAll('a[href*="logout.php"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetUrl = this.getAttribute('href');
        const frame = document.querySelector('.app-frame');
        if (frame) frame.classList.add('logging-out');

        let curtain = document.getElementById('logoutCurtain');
        if (!curtain) {
            curtain = document.createElement('div');
            curtain.id = 'logoutCurtain';
            curtain.className = 'logout-curtain';
            curtain.innerHTML = `
                <div class="curtain-box">
                    <div class="curtain-crown"><i class="fa-solid fa-right-from-bracket"></i></div>
                    <div class="curtain-badge">Mengakhiri Sesi Owner</div>
                    <h2>Sampai Jumpa Kembali</h2>
                    <p>Menutup sesi kerja dan kembali ke halaman sambutan...</p>
                    <div class="curtain-loader"><div class="logout-loader-fill"></div></div>
                </div>
            `;
            document.body.appendChild(curtain);
        }
        requestAnimationFrame(() => curtain.classList.add('active'));
        setTimeout(() => { window.location.href = targetUrl; }, 650);
    });
});
</script>
</body>
</html>