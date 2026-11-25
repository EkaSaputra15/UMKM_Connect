<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'superadmin') {
        header("Location: superadmin/dashboard.php");
    } else {
        header("Location: admin/dashboard.php");
    }
    exit;
}
header("Location: login.php");
exit;
?>