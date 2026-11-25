<?php
session_start();
$_SESSION = [];
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluar Sistem - UMKM Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            min-height: 100vh;
            background: radial-gradient(circle at center, #231059 0%, #11062B 65%, #070314 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            overflow: hidden;
        }
        .exit-wrap {
            text-align: center;
            max-width: 380px;
            width: 90%;
            animation: popOut 0.45s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes popOut {
            from { opacity: 0; transform: scale(0.92) translateY(12px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .exit-icon {
            width: 62px;
            height: 62px;
            margin: 0 auto 14px;
            border-radius: 18px;
            background: linear-gradient(135deg, #FFE600, #F5C400);
            color: #140931;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 28px rgba(255, 230, 0, 0.32);
        }
        h3 { font-size: 19px; font-weight: 800; margin-bottom: 5px; }
        p  { font-size: 12.5px; color: #C7C0E6; margin-bottom: 16px; }
        .bar {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.14);
            border-radius: 10px;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #FFE600, #7C5CFA);
            animation: barMove 0.45s ease forwards;
        }
        @keyframes barMove { to { width: 100%; } }
    </style>
</head>
<body>
    <div class="exit-wrap">
        <div class="exit-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
        <h3>Berhasil Keluar</h3>
        <p>Mengalihkan kembali ke halaman utama UMKM Connect...</p>
        <div class="bar"><div class="bar-fill"></div></div>
    </div>
    <script>
        setTimeout(() => {
            window.location.replace('login.php');
        }, 420);
    </script>
</body>
</html>