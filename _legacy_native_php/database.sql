DROP DATABASE IF EXISTS db_umkm_connect;
CREATE DATABASE db_umkm_connect;
USE db_umkm_connect;

-- 1. Tabel Users (Hanya 2 Role: superadmin = Owner, admin = Kasir)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin') NOT NULL DEFAULT 'admin',
    status_akun ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Pelanggan / Member CRM (Tanpa login, dikelola Kasir & Owner)
CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_wa VARCHAR(20) NOT NULL,
    tgl_lahir DATE NULL,
    poin INT DEFAULT 0,
    tier ENUM('Bronze', 'Silver', 'Gold') DEFAULT 'Bronze',
    status_aktif ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif',
    terakhir_transaksi DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Kategori Layanan
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    ikon VARCHAR(50) NOT NULL
);

-- 4. Tabel Katalog Layanan UMKM
CREATE TABLE layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    nama_layanan VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga INT NOT NULL,
    badge_warna VARCHAR(80) DEFAULT 'linear-gradient(135deg, #A1C4FD 0%, #C2E9FB 100%)',
    ikon_layanan VARCHAR(50) DEFAULT 'fa-briefcase',
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
);

-- 5. Tabel Transaksi (Mencatat Pelanggan & Kasir/Admin yang melayani)
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_nota VARCHAR(30) NOT NULL UNIQUE,
    pelanggan_id INT NOT NULL,
    kasir_id INT NOT NULL,
    total_harga INT NOT NULL,
    biaya_jasa INT DEFAULT 5000,
    poin_digunakan INT DEFAULT 0,
    diskon_poin INT DEFAULT 0,
    total_bayar INT NOT NULL,
    poin_didapat INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE,
    FOREIGN KEY (kasir_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Tabel Detail Transaksi
CREATE TABLE detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    layanan_id INT NULL,
    nama_layanan_snapshot VARCHAR(100) NOT NULL,
    qty INT DEFAULT 1,
    harga_satuan INT NOT NULL,
    subtotal INT NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE
);

-- 7. Tabel Riwayat Poin Membership
CREATE TABLE riwayat_poin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelanggan_id INT NOT NULL,
    jenis ENUM('Masuk', 'Keluar') NOT NULL,
    jumlah INT NOT NULL,
    keterangan VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE
);

-- ==========================================
-- DATA AWAL (SEEDER)
-- Password semua akun: password
-- ==========================================
INSERT INTO users (nama, email, password, role, status_akun) VALUES
('Owner UMKM Connect', 'owner@umkm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'Aktif'),
('Kasir Rina', 'kasir@umkm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Aktif'),
('Kasir Dimas', 'dimas@umkm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Aktif');

INSERT INTO pelanggan (nama, no_wa, tgl_lahir, poin, tier, status_aktif, terakhir_transaksi) VALUES
('Budi Santoso', '6281234560001', '1998-08-17', 1250, 'Gold', 'Aktif', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Siti Rahma', '6285798760002', '2000-02-14', 350, 'Silver', 'Aktif', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Ahmad Fauzi', '6281311110003', '1997-11-22', 80, 'Bronze', 'Tidak Aktif', DATE_SUB(NOW(), INTERVAL 40 DAY));

INSERT INTO kategori (nama_kategori, ikon) VALUES
('Desain', 'fa-pen-nib'),
('Servis', 'fa-screwdriver-wrench'),
('Kebersihan', 'fa-broom'),
('Konsultasi', 'fa-comments'),
('Percetakan', 'fa-print'),
('Fotografi', 'fa-camera');

INSERT INTO layanan (kategori_id, nama_layanan, deskripsi, harga, badge_warna, ikon_layanan) VALUES
(1, 'Desain Logo UMKM', 'Identitas logo profesional & file master siap cetak.', 150000, 'linear-gradient(135deg, #FF9A9E 0%, #FECFEF 100%)', 'fa-bezier-curve'),
(2, 'Servis AC & Listrik', 'Perawatan berkala, cuci AC, dan instalasi kelistrikan.', 120000, 'linear-gradient(135deg, #84FAB0 0%, #8FD3F4 100%)', 'fa-fan'),
(6, 'Foto Produk Katalog', 'Sesi foto studio & editing katalog marketplace.', 180000, 'linear-gradient(135deg, #A1C4FD 0%, #C2E9FB 100%)', 'fa-camera-retro'),
(3, 'Deep Cleaning Kantor', 'Pembersihan menyeluruh ruang usaha dan toko.', 200000, 'linear-gradient(135deg, #F6D365 0%, #FDA085 100%)', 'fa-sparkles'),
(4, 'Konsultasi Legal & NIB', 'Pendampingan NIB OSS, PIRT, dan sertifikasi halal.', 250000, 'linear-gradient(135deg, #D4FC79 0%, #96E6A1 100%)', 'fa-file-signature'),
(5, 'Cetak Kemasan & Stiker', 'Cetak label stiker vinyl anti air & kemasan box.', 95000, 'linear-gradient(135deg, #E0C3FC 0%, #8EC5FC 100%)', 'fa-box-open');

INSERT INTO transaksi (kode_nota, pelanggan_id, kasir_id, total_harga, biaya_jasa, poin_digunakan, diskon_poin, total_bayar, poin_didapat, created_at) VALUES
('INV-20261101', 1, 2, 450000, 5000, 0, 0, 455000, 45, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('INV-20261102', 2, 2, 150000, 5000, 0, 0, 155000, 15, DATE_SUB(NOW(), INTERVAL 5 DAY)),
('INV-20261001', 3, 3, 120000, 5000, 0, 0, 125000, 12, DATE_SUB(NOW(), INTERVAL 40 DAY));

INSERT INTO detail_transaksi (transaksi_id, layanan_id, nama_layanan_snapshot, qty, harga_satuan, subtotal) VALUES
(1, 1, 'Desain Logo UMKM', 1, 150000, 150000),
(1, 2, 'Servis AC & Listrik', 1, 120000, 120000),
(1, 3, 'Foto Produk Katalog', 1, 180000, 180000),
(2, 1, 'Desain Logo UMKM', 1, 150000, 150000),
(3, 2, 'Servis AC & Listrik', 1, 120000, 120000);