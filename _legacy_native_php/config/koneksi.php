<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_umkm_connect";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function formatNoWa($no_wa) {
    $no_wa = preg_replace('/[^0-9]/', '', $no_wa);
    if (substr($no_wa, 0, 1) === '0') {
        $no_wa = '62' . substr($no_wa, 1);
    }
    return $no_wa;
}

function sinkronTierPelanggan($conn, $pelanggan_id) {
    $q = mysqli_query($conn, "SELECT poin FROM pelanggan WHERE id = '$pelanggan_id'");
    if ($row = mysqli_fetch_assoc($q)) {
        $poin = (int)$row['poin'];
        $tier = 'Bronze';
        if ($poin >= 1000) {
            $tier = 'Gold';
        } elseif ($poin >= 300) {
            $tier = 'Silver';
        }
        mysqli_query($conn, "UPDATE pelanggan SET tier = '$tier' WHERE id = '$pelanggan_id'");
    }
}

function cekRole($role_wajib) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
    if ($_SESSION['role'] !== $role_wajib) {
        if ($_SESSION['role'] === 'superadmin') {
            header("Location: ../superadmin/dashboard.php");
        } else {
            header("Location: ../admin/dashboard.php");
        }
        exit;
    }
}
?>