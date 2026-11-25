<?php
require __DIR__ . '/../config/koneksi.php';
/** @var mysqli $conn */
cekRole('admin');

// Cek apakah baru saja login sebagai Admin Kasir
$baru_login = !empty($_SESSION['just_logged_in']);
unset($_SESSION['just_logged_in']);

$kasir_id = $_SESSION['user_id'];
$notif = '';

// 1. Proses Input Cepat Pelanggan Baru oleh Kasir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_member_baru'])) {
    $nama      = mysqli_real_escape_string($conn, $_POST['nama_baru']);
    $no_wa     = formatNoWa($_POST['wa_baru']);
    $email     = mysqli_real_escape_string($conn, $_POST['email_baru']);
    $tgl_lahir = mysqli_real_escape_string($conn, $_POST['tgl_lahir_baru']);

    mysqli_query($conn, "INSERT INTO pelanggan (nama, no_wa, email, tgl_lahir, poin, tier, status_aktif) 
                         VALUES ('$nama', '$no_wa', '$email', '$tgl_lahir', 50, 'Bronze', 'Aktif')");
    $new_pid = mysqli_insert_id($conn);
    mysqli_query($conn, "INSERT INTO riwayat_poin (pelanggan_id, jenis, jumlah, keterangan) 
                         VALUES ('$new_pid', 'Masuk', 50, 'Bonus Member Baru')");
    $notif = "Member baru <strong>$nama</strong> berhasil didaftarkan (+50 Poin)! Silakan pilih di dropdown pelanggan.";
}

// 2. Proses Simpan Transaksi Kasir & Kirim Nota WA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_transaksi_kasir'])) {
    $pelanggan_id = (int)$_POST['pelanggan_id'];
    $items        = json_decode($_POST['items_json'] ?? '[]', true);
    $pakai_poin   = isset($_POST['pakai_poin']) ? 1 : 0;

    $plg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = '$pelanggan_id'"));

    if ($plg && !empty($items)) {
        $subtotal_all = 0;
        foreach ($items as $it) {
            $subtotal_all += ((int)$it['harga'] * (int)$it['qty']);
        }

        $biaya_jasa = 5000;
        $poin_digunakan = 0;
        $diskon_poin = 0;

        if ($pakai_poin && $plg['poin'] > 0) {
            $maks_diskon = floor($subtotal_all * 0.5);
            $diskon_poin = min($maks_diskon, $plg['poin'] * 100);
            $poin_digunakan = ceil($diskon_poin / 100);
        }

        $total_bayar  = ($subtotal_all + $biaya_jasa) - $diskon_poin;
        $poin_didapat = floor($total_bayar / 10000);
        $kode_nota    = "INV-" . date("YmdHis");

        mysqli_query($conn, "INSERT INTO transaksi (kode_nota, pelanggan_id, kasir_id, total_harga, biaya_jasa, poin_digunakan, diskon_poin, total_bayar, poin_didapat) 
                             VALUES ('$kode_nota', '$pelanggan_id', '$kasir_id', '$subtotal_all', '$biaya_jasa', '$poin_digunakan', '$diskon_poin', '$total_bayar', '$poin_didapat')");
        $trx_id = mysqli_insert_id($conn);

        $daftar_item_wa = "";
        foreach ($items as $it) {
            $lid   = (int)$it['id'];
            $nama  = mysqli_real_escape_string($conn, $it['nama']);
            $qty   = (int)$it['qty'];
            $harga = (int)$it['harga'];
            $sub   = $qty * $harga;
            mysqli_query($conn, "INSERT INTO detail_transaksi (transaksi_id, layanan_id, nama_layanan_snapshot, qty, harga_satuan, subtotal) 
                                 VALUES ('$trx_id', '$lid', '$nama', '$qty', '$harga', '$sub')");
            $daftar_item_wa .= "• {$it['nama']} (x{$qty}) : " . formatRupiah($sub) . "\n";
        }

        $poin_akhir = $plg['poin'] - $poin_digunakan + $poin_didapat;
        mysqli_query($conn, "UPDATE pelanggan SET poin = '$poin_akhir', status_aktif = 'Aktif', terakhir_transaksi = NOW() WHERE id = '$pelanggan_id'");

        if ($poin_digunakan > 0) {
            mysqli_query($conn, "INSERT INTO riwayat_poin (pelanggan_id, jenis, jumlah, keterangan) 
                                 VALUES ('$pelanggan_id', 'Keluar', '$poin_digunakan', 'Penukaran diskon nota $kode_nota')");
        }
        if ($poin_didapat > 0) {
            mysqli_query($conn, "INSERT INTO riwayat_poin (pelanggan_id, jenis, jumlah, keterangan) 
                                 VALUES ('$pelanggan_id', 'Masuk', '$poin_didapat', 'Reward transaksi $kode_nota')");
        }

        sinkronTierPelanggan($conn, $pelanggan_id);

        $teks_nota = "Halo *{$plg['nama']}*! Terima kasih telah menggunakan layanan *UMKM Connect*.\n\n"
                   . "🧾 *NOTA DIGITAL TRANSAKSI*\n"
                   . "No. Nota: *{$kode_nota}*\n"
                   . "Kasir: {$_SESSION['nama']}\n"
                   . "---------------------------------\n"
                   . $daftar_item_wa
                   . "Biaya Jasa: " . formatRupiah($biaya_jasa) . "\n"
                   . "Diskon Poin: -" . formatRupiah($diskon_poin) . "\n"
                   . "*Total Bayar: " . formatRupiah($total_bayar) . "*\n"
                   . "---------------------------------\n"
                   . "Poin Didapat: +{$poin_didapat} Poin\n"
                   . "Saldo Poin Member: *{$poin_akhir} Poin*";

        $wa_url = "https://wa.me/{$plg['no_wa']}?text=" . urlencode($teks_nota);
        $notif = "Transaksi <strong>$kode_nota</strong> berhasil disimpan! <a href='$wa_url' target='_blank' style='color:#5B35E5; font-weight:800; text-decoration:underline;'><i class='fa-brands fa-whatsapp'></i> Klik untuk Kirim Nota Digital ke WhatsApp Pelanggan</a>";
    }
}

$kat_aktif = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$cari      = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';

$where = "WHERE 1=1";
if ($kat_aktif > 0) $where .= " AND kategori_id = '$kat_aktif'";
if ($cari !== '')   $where .= " AND nama_layanan LIKE '%$cari%'";

$kategori_list  = mysqli_query($conn, "SELECT * FROM kategori");
$layanan_list   = mysqli_query($conn, "SELECT * FROM layanan $where ORDER BY id ASC");
$pelanggan_list = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir (Admin) - UMKM Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=15">
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
<!-- TIRAI ANIMASI SAAT BARU MASUK LOGIN SEBAGAI KASIR -->
<div class="owner-welcome-curtain" id="kasirCurtain">
    <div class="curtain-box">
        <div class="curtain-crown"><i class="fa-solid fa-cash-register"></i></div>
        <div class="curtain-badge">Akses Operator Kasir Terverifikasi</div>
        <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?></h2>
        <p>Menyiapkan katalog layanan, poin membership, dan kasir nota digital...</p>
        <div class="curtain-loader">
            <div class="curtain-loader-fill"></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="app-frame">
    <!-- NAVIGASI ATAS KASIR -->
    <aside class="sidebar">
        <div>
            <div class="brand-logo">UMKM Connect</div>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="active"><i class="fa-solid fa-cash-register"></i> Kasir & Transaksi</a></li>
                <li><a href="pelanggan.php"><i class="fa-solid fa-users"></i> Data Pelanggan</a></li>
                <li><a href="transaksi.php"><i class="fa-solid fa-receipt"></i> Riwayat & Nota WA</a></li>
                <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
            </ul>
        </div>

        <div class="sidebar-promo-card">
            <span class="badge-tag">ROLE OPERATOR</span>
            <h4>ADMIN (KASIR)</h4>
        </div>
    </aside>

    <!-- KONTEN TENGAH KASIR -->
    <main class="main-content">
        <div class="topbar">
            <h1>Halo, <?= htmlspecialchars($_SESSION['nama']) ?></h1>
            <div class="topbar-right">
                <form method="GET" class="search-box">
                    <i class="fa-solid fa-magnifying-glass" style="color:#5B35E5;"></i>
                    <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari layanan UMKM...">
                </form>
                <div class="avatar-pill"><i class="fa-solid fa-headset"></i> Kasir</div>
            </div>
        </div>

        <?php if ($notif): ?>
            <div class="alert alert-success"><?= $notif ?></div>
        <?php endif; ?>

        <!-- Banner Unggulan Kasir 3D -->
        <div class="hero-banner tilt-3d">
            <div class="card-glare"></div>
            <div>
                <h2>LAYANAN UNGGULAN<br>SOLUSI TERBAIK UMKM</h2>
                <p>PILIH LAYANAN PELANGGAN & KELOLA POIN MEMBERSHIP OTOMATIS</p>
            </div>
            <div class="hero-icons">
                <div class="hero-icon-box"><i class="fa-solid fa-cash-register"></i></div>
                <div class="hero-icon-box"><i class="fa-brands fa-whatsapp"></i></div>
            </div>
        </div>

        <!-- Kategori Layanan -->
        <div class="white-card">
            <div class="section-header">
                <h3><i class="fa-solid fa-layer-group"></i> Kategori Layanan</h3>
                <a href="dashboard.php">Semua Kategori</a>
            </div>
            <div class="category-grid">
                <?php while ($k = mysqli_fetch_assoc($kategori_list)): ?>
                    <a href="dashboard.php?kategori=<?= $k['id'] ?>" class="cat-card <?= ($kat_aktif == $k['id']) ? 'active' : '' ?>">
                        <i class="fa-solid <?= htmlspecialchars($k['ikon']) ?>"></i>
                        <span><?= htmlspecialchars($k['nama_kategori']) ?></span>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Layanan Populer 3D -->
        <div class="white-card">
            <div class="section-header">
                <h3><i class="fa-solid fa-sparkles"></i> Pilih Layanan Pelanggan</h3>
                <a href="dashboard.php">Reset Filter</a>
            </div>
            <div class="service-grid">
                <?php while ($l = mysqli_fetch_assoc($layanan_list)): ?>
                    <div class="service-card tilt-3d">
                        <div class="card-glare"></div>
                        <div>
                            <div class="service-thumb" style="background: <?= htmlspecialchars($l['badge_warna']) ?>;">
                                <i class="fa-solid <?= htmlspecialchars($l['ikon_layanan']) ?>"></i>
                            </div>
                            <div class="service-stars">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                            </div>
                            <div class="service-title"><?= htmlspecialchars($l['nama_layanan']) ?></div>
                        </div>
                        <div class="service-footer">
                            <span class="service-price"><?= formatRupiah($l['harga']) ?></span>
                            <button type="button" class="btn-add"
                                onclick="tambahLayanan(<?= $l['id'] ?>, '<?= addslashes($l['nama_layanan']) ?>', <?= $l['harga'] ?>)">
                                +
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <!-- PANEL KANAN KASIR: PILIH PELANGGAN, POIN & RINGKASAN TRANSAKSI -->
    <aside class="right-panel">
        <!-- Kartu Poin Pelanggan Terpilih 3D -->
        <div class="points-card tilt-3d">
            <div class="card-glare"></div>
            <div class="points-top">
                <div class="points-box">
                    <small><i class="fa-solid fa-award"></i> Poin Member Terpilih</small>
                    <strong id="displayPoinMember">0 Poin</strong>
                </div>
                <button type="button" onclick="toggleFormMemberBaru()" class="btn-pill-yellow">
                    + Member Baru
                </button>
            </div>
        </div>

        <!-- Form Cepat Input Pelanggan Baru -->
        <div class="white-card" id="boxMemberBaru" style="display:none; border: 2px solid #5B35E5;">
            <h4 style="font-size:14px; margin-bottom:10px; color:#18142B;">Registrasi Pelanggan Baru</h4>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="nama_baru" class="form-control" placeholder="Nama Lengkap Pelanggan" required>
                </div>
                <div class="form-group">
                    <input type="text" name="wa_baru" class="form-control" placeholder="No WhatsApp (0812...)" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email_baru" class="form-control" placeholder="Alamat Email (nama@gmail.com)" required>
                </div>
                <div class="form-group">
                    <input type="date" name="tgl_lahir_baru" class="form-control" required>
                </div>
                <button type="submit" name="simpan_member_baru" class="btn-dark-sm" style="width:100%;">Simpan Profil (+50 Poin)</button>
            </form>
        </div>

        <!-- Ringkasan Transaksi Layanan -->
        <div class="white-card" style="flex:1;">
            <div class="section-header" style="margin-bottom:8px;">
                <h3><i class="fa-solid fa-bag-shopping"></i> Ringkasan Layanan</h3>
                <button type="button" onclick="resetCart()" style="background:none;border:none;color:#E11D48;font-size:11.5px;font-weight:700;cursor:pointer;">Reset</button>
            </div>

            <form method="POST" id="formPosKasir">
                <div class="form-group">
                    <label>Profil Pelanggan Member</label>
                    <select name="pelanggan_id" id="selectPelanggan" class="form-control" onchange="renderPos()" required>
                        <?php while ($p = mysqli_fetch_assoc($pelanggan_list)): ?>
                            <option value="<?= $p['id'] ?>" data-poin="<?= $p['poin'] ?>" data-tier="<?= $p['tier'] ?>">
                                <?= htmlspecialchars($p['nama']) ?> (<?= $p['tier'] ?> - <?= $p['poin'] ?> Poin)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="cart-items" id="cartList">
                    <p style="font-size:12px; color:#8E8CA7; text-align:center; padding:16px 0;">Klik tombol <strong>+</strong> pada layanan di tengah.</p>
                </div>

                <input type="hidden" name="items_json" id="itemsJson">

                <div class="summary-row">
                    <span>Biaya Jasa</span>
                    <span id="posBiaya">Rp 0</span>
                </div>
                <div class="summary-row" id="rowDiskonPos" style="display:none; color:#5B35E5; font-weight:700;">
                    <span>Potongan Poin</span>
                    <span id="posDiskon">- Rp 0</span>
                </div>
                <div class="summary-row total">
                    <span>Total Bayar</span>
                    <span id="posTotal">Rp 0</span>
                </div>

                <label class="promo-toggle" style="cursor:pointer;">
                    <span><i class="fa-solid fa-ticket" style="color:#FFE600;margin-right:6px;"></i> Gunakan Poin / Reward</span>
                    <input type="checkbox" name="pakai_poin" id="cekPoinPos" onchange="renderPos()">
                </label>

                <button type="submit" name="simpan_transaksi_kasir" class="btn-primary-full">
                    Simpan & Kirim Nota WA <i class="fa-brands fa-whatsapp"></i>
                </button>
            </form>
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

// Tirai Animasi saat Login Kasir
const kasirCurtain = document.getElementById('kasirCurtain');
if (kasirCurtain) {
    setTimeout(() => {
        kasirCurtain.classList.add('curtain-hide');
        setTimeout(() => kasirCurtain.remove(), 700);
    }, 1150);
}

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
                    <div class="curtain-badge">Mengakhiri Sesi Kasir</div>
                    <h2>Sampai Jumpa Kembali</h2>
                    <p>Menutup sesi kerja dan kembali ke halaman sambutan...</p>
                    <div class="curtain-loader">
                        <div class="logout-loader-fill"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(curtain);
        }

        requestAnimationFrame(() => {
            curtain.classList.add('active');
        });

        setTimeout(() => {
            window.location.href = targetUrl;
        }, 650);
    });
});

let cart = [];

function formatRp(num) {
    return 'Rp ' + num.toLocaleString('id-ID');
}

function toggleFormMemberBaru() {
    const box = document.getElementById('boxMemberBaru');
    box.style.display = (box.style.display === 'none') ? 'block' : 'none';
}

function tambahLayanan(id, nama, harga) {
    const idx = cart.findIndex(item => item.id === id);
    if (idx > -1) {
        cart[idx].qty += 1;
    } else {
        cart.push({ id, nama, harga, qty: 1 });
    }
    renderPos();
}

function resetCart() {
    cart = [];
    renderPos();
}

function renderPos() {
    const selPlg = document.getElementById('selectPelanggan');
    const poinMember = parseInt(selPlg.options[selPlg.selectedIndex]?.dataset.poin || 0);
    document.getElementById('displayPoinMember').innerText = poinMember.toLocaleString('id-ID') + ' Poin';

    const listEl = document.getElementById('cartList');
    const itemsJson = document.getElementById('itemsJson');
    const pakaiPoin = document.getElementById('cekPoinPos').checked;

    if (cart.length === 0) {
        listEl.innerHTML = '<p style="font-size:12px; color:#8E8CA7; text-align:center; padding:16px 0;">Klik tombol <strong>+</strong> pada layanan di tengah.</p>';
        document.getElementById('posBiaya').innerText = 'Rp 0';
        document.getElementById('posTotal').innerText = 'Rp 0';
        document.getElementById('rowDiskonPos').style.display = 'none';
        itemsJson.value = '[]';
        return;
    }

    let html = '';
    let subtotal = 0;
    cart.forEach(item => {
        const sub = item.harga * item.qty;
        subtotal += sub;
        html += `
            <div class="cart-item">
                <div>
                    <strong style="color:#18142B;">${item.nama}</strong><br>
                    <small style="color:#8E8CA7;">Qty: x${item.qty}</small>
                </div>
                <strong style="color:#5B35E5;">${formatRp(sub)}</strong>
            </div>
        `;
    });

    listEl.innerHTML = html;
    const biaya = 5000;
    let diskon = 0;

    if (pakaiPoin && poinMember > 0) {
        diskon = Math.min(Math.floor(subtotal * 0.5), poinMember * 100);
        document.getElementById('rowDiskonPos').style.display = 'flex';
        document.getElementById('posDiskon').innerText = '- ' + formatRp(diskon);
    } else {
        document.getElementById('rowDiskonPos').style.display = 'none';
    }

    const total = (subtotal + biaya) - diskon;
    document.getElementById('posBiaya').innerText = formatRp(biaya);
    document.getElementById('posTotal').innerText = formatRp(total);
    itemsJson.value = JSON.stringify(cart);
}

renderPos();
</script>
</body>
</html>