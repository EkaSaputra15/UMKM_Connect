<?php
require __DIR__ . '/../config/koneksi.php';
/** @var mysqli $conn */
cekRole('superadmin');

// Cek apakah baru saja login sebagai Owner
$baru_login = !empty($_SESSION['just_logged_in']);
unset($_SESSION['just_logged_in']);

// Statistik Utama Owner
$tot_pelanggan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM pelanggan"))['jml'];
$tot_omzet     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_bayar),0) AS omzet FROM transaksi"))['omzet'];
$tot_transaksi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM transaksi"))['jml'];

// Data Grafik Pendapatan
$grafik_labels = [];
$grafik_data   = [];
$q_grafik = mysqli_query($conn, "SELECT DATE_FORMAT(MIN(created_at), '%b %Y') AS bulan, SUM(total_bayar) AS total 
                                 FROM transaksi 
                                 GROUP BY YEAR(created_at), MONTH(created_at) 
                                 ORDER BY YEAR(created_at) ASC, MONTH(created_at) ASC LIMIT 6");
while ($g = mysqli_fetch_assoc($q_grafik)) {
    $grafik_labels[] = $g['bulan'];
    $grafik_data[]   = (int)$g['total'];
}

// 4 Transaksi Terbaru
$trx_terbaru = mysqli_query($conn, "SELECT t.kode_nota, t.total_bayar, p.nama AS nama_pelanggan 
                                    FROM transaksi t 
                                    JOIN pelanggan p ON t.pelanggan_id = p.id 
                                    ORDER BY t.id DESC LIMIT 4");

// Top Pelanggan Member
$top_pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY poin DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - UMKM Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=15">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- OBJEK GEOMETRIS 3D MELAYANG DI LATAR -->
<div class="bg-3d-shapes">
    <div class="ring-3d r1"></div>
    <div class="ring-3d r2"></div>
    <div class="sphere-3d gold-s1"></div>
    <div class="sphere-3d purple-s2"></div>
</div>

<?php if ($baru_login): ?>
<!-- TIRAI SAMBUTAN ANIMASI SAAT MASUK LOGIN SEBAGAI OWNER -->
<div class="owner-welcome-curtain" id="ownerCurtain">
    <div class="curtain-box">
        <div class="curtain-crown"><i class="fa-solid fa-crown"></i></div>
        <div class="curtain-badge">Akses Superadmin Terverifikasi</div>
        <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?></h2>
        <p>Menyiapkan ringkasan omzet, data member, dan kontrol operasional...</p>
        <div class="curtain-loader">
            <div class="curtain-loader-fill"></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="app-frame">
    <!-- NAVIGASI ATAS -->
    <aside class="sidebar">
        <div>
            <div class="brand-logo">UMKM Connect</div>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="active"><i class="fa-solid fa-house"></i> Dashboard</a></li>
                <li><a href="transaksi.php"><i class="fa-solid fa-file-invoice-dollar"></i> Transaksi</a></li>
                <li><a href="pelanggan.php"><i class="fa-solid fa-users"></i> Data Member</a></li>
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

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <div class="hero-cockpit tilt-3d">
            <div class="card-glare"></div>
            <div>
                <div class="hero-pill">
                    <span class="pulse-green"></span> Ringkasan Operasional
                </div>
                <h1 id="sapaanWaktu">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></h1>
                <p>Pantau pemasukan harian, riwayat transaksi layanan, dan poin pelanggan member Anda.</p>
            </div>

            <div class="hero-actions">
                <div class="live-clock-box">
                    <strong id="jamDigital">00:00:00</strong>
                    <span id="tanggalDigital">Memuat tanggal...</span>
                </div>
                <a href="layanan.php" class="btn-hero-action">
                    <i class="fa-solid fa-plus"></i> Kelola Layanan
                </a>
            </div>
        </div>

        <!-- 3 Kartu Ringkasan 3D -->
        <div class="stat-cards-3">
            <div class="stat-card purple tilt-3d">
                <div class="card-glare"></div>
                <div class="stat-card-top">
                    <div class="stat-icon-orb"><i class="fa-solid fa-wallet"></i></div>
                    <span class="stat-trend-tag">Pemasukan</span>
                </div>
                <small>Total Omzet Pendapatan</small>
                <h2 class="count-up" data-target="<?= (int)$tot_omzet ?>" data-prefix="Rp " data-suffix=""><?= formatRupiah($tot_omzet) ?></h2>
                <div class="stat-bar-track">
                    <div class="stat-bar-fill" style="width: 82%;"></div>
                </div>
            </div>

            <div class="stat-card yellow tilt-3d">
                <div class="card-glare"></div>
                <div class="stat-card-top">
                    <div class="stat-icon-orb"><i class="fa-solid fa-users"></i></div>
                    <span class="stat-trend-tag">Terdaftar</span>
                </div>
                <small>Total Pelanggan Member</small>
                <h2 class="count-up" data-target="<?= (int)$tot_pelanggan ?>" data-prefix="" data-suffix=" Member"><?= $tot_pelanggan ?> Member</h2>
                <div class="stat-bar-track">
                    <div class="stat-bar-fill" style="width: 68%;"></div>
                </div>
            </div>

            <div class="stat-card white tilt-3d">
                <div class="card-glare"></div>
                <div class="stat-card-top">
                    <div class="stat-icon-orb"><i class="fa-solid fa-receipt"></i></div>
                    <span class="stat-trend-tag">Selesai</span>
                </div>
                <small>Total Transaksi Layanan</small>
                <h2 class="count-up" data-target="<?= (int)$tot_transaksi ?>" data-prefix="" data-suffix=" Nota"><?= $tot_transaksi ?> Nota</h2>
                <div class="stat-bar-track">
                    <div class="stat-bar-fill" style="width: 75%;"></div>
                </div>
            </div>
        </div>

        <!-- Grafik & Transaksi Terbaru -->
        <div class="admin-mid-grid">
            <div class="white-card">
                <div class="section-header">
                    <h3><i class="fa-solid fa-chart-line"></i> Grafik Pendapatan</h3>
                    <span style="font-size:11.5px; font-weight:600; color:#6E6A8A;">6 Bulan Terakhir</span>
                </div>
                <canvas id="ownerChart" height="150"></canvas>
            </div>

            <div class="white-card">
                <div class="section-header">
                    <h3><i class="fa-solid fa-clock-rotate-left"></i> Transaksi Terbaru</h3>
                    <a href="transaksi.php">Semua Nota</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($tr = mysqli_fetch_assoc($trx_terbaru)): ?>
                        <tr>
                            <td class="nowrap-cell"><span class="nota-badge"><?= $tr['kode_nota'] ?></span></td>
                            <td><strong><?= htmlspecialchars($tr['nama_pelanggan']) ?></strong></td>
                            <td class="nowrap-cell" style="font-weight:800; color:#140931;"><?= formatRupiah($tr['total_bayar']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pelanggan Setia -->
        <div class="white-card">
            <div class="section-header">
                <h3><i class="fa-solid fa-star"></i> Pelanggan Setia & Poin Member</h3>
                <a href="pelanggan.php">Semua Member</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Email Member</th>
                        <th>Level Member</th>
                        <th>Saldo Poin</th>
                        <th>Status</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($top_pelanggan)):
                        $inisial = strtoupper(substr($p['nama'], 0, 1));
                        $email_plg = $p['email'] ?? '';
                    ?>
                    <tr>
                        <td>
                            <div class="member-cell">
                                <div class="member-avatar"><?= $inisial ?></div>
                                <strong><?= htmlspecialchars($p['nama']) ?></strong>
                            </div>
                        </td>
                        <td class="nowrap-cell" style="color:#6E6A8A;"><?= htmlspecialchars($email_plg ?: '-') ?></td>
                        <td><span class="badge-tier tier-<?= $p['tier'] ?>"><?= $p['tier'] ?></span></td>
                        <td class="nowrap-cell"><strong style="color:#5B35E5;"><?= number_format($p['poin'], 0, ',', '.') ?> Poin</strong></td>
                        <td class="nowrap-cell" style="color: <?= $p['status_aktif'] === 'Aktif' ? '#5B35E5' : '#9894B5' ?>; font-weight:700;">
                            ● <?= $p['status_aktif'] ?>
                        </td>
                        <td class="nowrap-cell">
                            <?php if ($email_plg): ?>
                                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($email_plg) ?>" target="_blank" class="btn-wa-mini" title="Kirim Email">
                                    <i class="fa-solid fa-envelope"></i>
                                </a>
                            <?php else: ?>
                                <span style="color:#9894B5; font-size:11px;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- PANEL KANAN: BROADCAST PROMO EMAIL -->
    <aside class="right-panel">
        <div class="points-card tilt-3d">
            <div class="card-glare"></div>
            <small><i class="fa-solid fa-envelope-open-text"></i>  BROADCAST EMAIL</small>
            <h3 style="font-size:17px; margin-top:6px;">Kirim Penawaran Member</h3>
            <p style="font-size:12px; margin-top:5px; line-height:1.5;">Pilih pelanggan dan kirim pesan promo langsung ke alamat email mereka.</p>
        </div>

        <div class="white-card">
            <div class="form-group">
                <label>Target Email Pelanggan</label>
                <select id="targetEmailOwner" class="form-control">
                    <?php
                    $all_p = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama ASC");
                    while ($ap = mysqli_fetch_assoc($all_p)):
                        $em = $ap['email'] ?? '';
                    ?>
                        <option value="<?= htmlspecialchars($em) ?>">
                            <?= htmlspecialchars($ap['nama']) ?> — <?= htmlspecialchars($em ?: 'Belum ada email') ?> (<?= $ap['tier'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Subjek Email</label>
                <input type="text" id="subjekEmailOwner" class="form-control" value="Promo Spesial Member UMKM Connect!">
            </div>

            <div class="form-group">
                <label>Pilih Contoh Pesan</label>
                <div class="preset-chips">
                    <button type="button" class="preset-chip" onclick="pilihTemplate('diskon')">Diskon Spesial</button>
                    <button type="button" class="preset-chip" onclick="pilihTemplate('poin')">Tukar Poin</button>
                    <button type="button" class="preset-chip" onclick="pilihTemplate('sapa')">Sapa Member</button>
                </div>
                <textarea id="pesanEmailOwner" class="form-control" rows="5">Halo Kak! Khusus Member UMKM Connect, dapatkan potongan harga spesial layanan minggu ini dengan menukarkan poin membership Anda. Yuk booking sekarang!</textarea>
            </div>

            <button type="button" onclick="kirimPromoEmailOwner()" class="btn-primary-full">
                <i class="fa-solid fa-paper-plane"></i> Kirim Promo via Email
            </button>
        </div>
    </aside>
</div>

<script>
// Efek 3D Tilt & Glare Interaktif mengikuti Kursor
document.querySelectorAll('.tilt-3d').forEach(card => {
    const glare = card.querySelector('.card-glare');
    card.addEventListener('mousemove', e => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = ((y - centerY) / centerY) * -6.5;
        const rotateY = ((x - centerX) / centerX) * 6.5;

        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-4px)`;
        if (glare) {
            glare.style.setProperty('--gx', `${(x / rect.width) * 100}%`);
            glare.style.setProperty('--gy', `${(y / rect.height) * 100}%`);
        }
    });

    card.addEventListener('mouseleave', () => {
        card.style.transform = '';
    });
});

// Animasi Angka Naik (Count-Up)
function animateCountUp() {
    document.querySelectorAll('.count-up').forEach(el => {
        const target = parseInt(el.getAttribute('data-target')) || 0;
        const prefix = el.getAttribute('data-prefix') || '';
        const suffix = el.getAttribute('data-suffix') || '';
        const duration = 1100;
        const startTime = performance.now();

        function update(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(target * ease);
            el.textContent = prefix + current.toLocaleString('id-ID') + suffix;
            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                el.textContent = prefix + target.toLocaleString('id-ID') + suffix;
            }
        }
        requestAnimationFrame(update);
    });
}

// Buka Tirai Sambutan Owner Jika Baru Login
const curtain = document.getElementById('ownerCurtain');
if (curtain) {
    setTimeout(() => {
        curtain.classList.add('curtain-hide');
        animateCountUp();
        setTimeout(() => curtain.remove(), 700);
    }, 1150);
} else {
    animateCountUp();
}

// Animasi Tirai Keluar saat Tombol Keluar Ditekan
document.querySelectorAll('a[href*="logout.php"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetUrl = this.getAttribute('href');
        const frame = document.querySelector('.app-frame');
        if (frame) frame.classList.add('logging-out');

        let logoutCurtain = document.getElementById('logoutCurtain');
        if (!logoutCurtain) {
            logoutCurtain = document.createElement('div');
            logoutCurtain.id = 'logoutCurtain';
            logoutCurtain.className = 'logout-curtain';
            logoutCurtain.innerHTML = `
                <div class="curtain-box">
                    <div class="curtain-crown"><i class="fa-solid fa-right-from-bracket"></i></div>
                    <div class="curtain-badge">Mengakhiri Sesi Owner</div>
                    <h2>Sampai Jumpa Kembali</h2>
                    <p>Menutup sesi kerja dan kembali ke halaman sambutan...</p>
                    <div class="curtain-loader">
                        <div class="logout-loader-fill"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(logoutCurtain);
        }

        requestAnimationFrame(() => {
            logoutCurtain.classList.add('active');
        });

        setTimeout(() => {
            window.location.href = targetUrl;
        }, 650);
    });
});

function updateJamCockpit() {
    const now = new Date();
    const jam = now.getHours();
    let sapaan = 'Selamat Malam';
    if (jam >= 4 && jam < 11) sapaan = 'Selamat Pagi';
    else if (jam >= 11 && jam < 15) sapaan = 'Selamat Siang';
    else if (jam >= 15 && jam < 19) sapaan = 'Selamat Sore';

    const elSapa = document.getElementById('sapaanWaktu');
    if (elSapa) elSapa.innerText = sapaan + ', <?= htmlspecialchars($_SESSION['nama']) ?>';

    const elJam = document.getElementById('jamDigital');
    const elTgl = document.getElementById('tanggalDigital');
    if (elJam) elJam.innerText = now.toLocaleTimeString('id-ID', { hour12: false });
    if (elTgl) elTgl.innerText = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
}
updateJamCockpit();
setInterval(updateJamCockpit, 1000);

const ctx = document.getElementById('ownerChart').getContext('2d');
const areaGradient = ctx.createLinearGradient(0, 0, 0, 220);
areaGradient.addColorStop(0, 'rgba(91, 53, 229, 0.16)');
areaGradient.addColorStop(1, 'rgba(91, 53, 229, 0.0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($grafik_labels ?: ['Bulan Ini']) ?>,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: <?= json_encode($grafik_data ?: [0]) ?>,
            borderColor: '#5B35E5',
            borderWidth: 2.5,
            backgroundColor: areaGradient,
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#FFE600',
            pointBorderColor: '#140931',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        animation: {
            duration: 1300,
            easing: 'easeOutQuart'
        },
        plugins: { legend: { display: false } },
        scales: {
            x: {
                ticks: { color: '#6E6A8A' },
                grid: { display: false }
            },
            y: {
                beginAtZero: true,
                ticks: { color: '#6E6A8A' },
                grid: { color: '#F1EEFA' }
            }
        }
    }
});

function pilihTemplate(jenis) {
    const subjek = document.getElementById('subjekEmailOwner');
    const box = document.getElementById('pesanEmailOwner');
    if (jenis === 'diskon') {
        subjek.value = "Diskon 20% Spesial Member UMKM Connect!";
        box.value = "Halo Kak! Khusus Member UMKM Connect, dapatkan diskon 20% untuk semua layanan hari ini. Tunjukkan email ini saat transaksi ya!";
    } else if (jenis === 'poin') {
        subjek.value = "Saatnya Tukar Poin Membership UMKM Connect Anda";
        box.value = "Halo Kak! Saldo poin membership UMKM Connect Anda sudah bisa ditukarkan dengan potongan harga spesial. Yuk klaim sebelum akhir bulan!";
    } else if (jenis === 'sapa') {
        subjek.value = "Penawaran Layanan Baru & Bonus Poin Ganda";
        box.value = "Halo Kak! Sudah lama nih belum mampir ke UMKM Connect. Kami punya layanan baru dengan bonus poin ganda khusus untuk Anda!";
    }
}

function kirimPromoEmailOwner() {
    const email = document.getElementById('targetEmailOwner').value.trim();
    const subjek = document.getElementById('subjekEmailOwner').value;
    const msg = document.getElementById('pesanEmailOwner').value;

    if (!email) {
        alert('Pelanggan ini belum memiliki alamat email. Silakan tambahkan email terlebih dahulu di menu Data Member.');
        return;
    }

    const gmailUrl = 'https://mail.google.com/mail/?view=cm&fs=1&to='
        + encodeURIComponent(email)
        + '&su=' + encodeURIComponent(subjek)
        + '&body=' + encodeURIComponent(msg);

    window.open(gmailUrl, '_blank');
}
</script>
</body>
</html>