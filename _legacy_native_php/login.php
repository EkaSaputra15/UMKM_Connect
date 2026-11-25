<?php
require __DIR__ . '/config/koneksi.php';
/** @var mysqli $conn */

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if ($user = mysqli_fetch_assoc($res)) {
        if ($user['status_akun'] !== 'Aktif') {
            $error = "Akun Anda dinonaktifkan oleh Superadmin (Owner).";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id']        = $user['id'];
            $_SESSION['nama']           = $user['nama'];
            $_SESSION['role']           = $user['role'];
            $_SESSION['just_logged_in'] = true;

            if ($user['role'] === 'superadmin') {
                header("Location: superadmin/dashboard.php");
            } else {
                header("Location: admin/dashboard.php");
            }
            exit;
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Email tidak terdaftar dalam sistem internal UMKM Connect.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - UMKM Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(-45deg, #070314, #170A3D, #2E1275, #100629, #210E59);
            background-size: 400% 400%;
            animation: auroraFlow 14s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        @keyframes auroraFlow {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .bg-pattern {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.13) 1px, transparent 1px);
            background-size: 28px 28px;
            animation: driftPattern 25s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes driftPattern {
            from { transform: translateY(0px); }
            to   { transform: translateY(-28px); }
        }

        /* =====================================================
           ORBS & CINCIN KACA 3D MELAYANG DI LATAR BELAKANG
           ===================================================== */
        .orb {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .orb-1 {
            width: 360px;
            height: 360px;
            top: -90px;
            left: -70px;
            background: radial-gradient(circle at 35% 35%, rgba(116, 75, 255, 0.65), rgba(91, 53, 229, 0.08) 70%);
            filter: blur(8px);
            animation: floatOrb1 9s ease-in-out infinite;
        }

        .orb-2 {
            width: 300px;
            height: 300px;
            bottom: -70px;
            right: -40px;
            background: radial-gradient(circle at 30% 30%, rgba(145, 94, 255, 0.55), rgba(39, 19, 94, 0.05) 70%);
            filter: blur(6px);
            animation: floatOrb2 11s ease-in-out infinite;
        }

        .orb-3 {
            width: 90px;
            height: 90px;
            top: 11%;
            right: 11%;
            background: radial-gradient(circle at 30% 30%, #FFF59D, #FFE600 55%, #B88E00 100%);
            box-shadow:
                inset -6px -8px 16px rgba(120, 85, 0, 0.45),
                0 15px 45px rgba(255, 230, 0, 0.48);
            animation: floatOrb3 7s ease-in-out infinite;
        }

        .glass-ring-3d {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
            border: 14px solid rgba(255, 255, 255, 0.09);
            box-shadow:
                inset 0 3px 8px rgba(255, 255, 255, 0.25),
                0 18px 36px rgba(0, 0, 0, 0.35);
        }

        .ring-left-3d {
            width: 150px;
            height: 150px;
            bottom: 10%;
            left: 7%;
            animation: floatOrb1 10s ease-in-out infinite;
        }

        .ring-right-3d {
            width: 110px;
            height: 110px;
            top: 16%;
            left: 14%;
            border-width: 10px;
            animation: floatOrb2 8.5s ease-in-out infinite;
        }

        @keyframes floatOrb1 {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50%      { transform: translate3d(24px, 20px, 0) scale(1.05); }
        }

        @keyframes floatOrb2 {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50%      { transform: translate3d(-20px, -22px, 0) scale(1.04); }
        }

        @keyframes floatOrb3 {
            0%, 100% { transform: translate3d(0, 0, 0) rotate(0deg); }
            50%      { transform: translate3d(-16px, 22px, 0) rotate(12deg); }
        }

        @keyframes spinSlow {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        /* Pantulan Cahaya 3D Mengikuti Kursor */
        .card-glare {
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: radial-gradient(circle at var(--gx, 50%) var(--gy, 50%), rgba(255, 255, 255, 0.28) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.25s ease;
            z-index: 6;
        }

        .tilt-3d:hover .card-glare {
            opacity: 1;
        }

        /* =====================================================
           1. LAYAR SAMBUTAN 3D BERLAPIS
           ===================================================== */
        .welcome-overlay {
            position: fixed;
            inset: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: radial-gradient(circle at center, rgba(22, 10, 61, 0.52) 0%, rgba(7, 3, 20, 0.85) 100%);
            backdrop-filter: blur(12px);
            animation: overlayFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            transition: opacity 0.75s cubic-bezier(0.22, 1, 0.36, 1),
                        transform 0.75s cubic-bezier(0.22, 1, 0.36, 1),
                        filter 0.75s ease;
        }

        @keyframes overlayFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .welcome-overlay.exit-portal {
            opacity: 0;
            transform: scale(1.16);
            filter: blur(14px);
            pointer-events: none;
        }

        .welcome-overlay.hidden-screen {
            display: none;
        }

        .welcome-float-wrap {
            max-width: 470px;
            width: 100%;
            animation: welcomeCardAppear 0.85s cubic-bezier(0.16, 1, 0.3, 1) forwards,
                       welcomeFloat 4.8s ease-in-out 0.85s infinite;
        }

        @keyframes welcomeCardAppear {
            0% {
                opacity: 0;
                transform: perspective(1100px) rotateX(12deg) translateY(45px) scale(0.9);
                filter: blur(8px);
            }
            100% {
                opacity: 1;
                transform: perspective(1100px) rotateX(0deg) translateY(0px) scale(1);
                filter: blur(0px);
            }
        }

        @keyframes welcomeFloat {
            0%, 100% { transform: translateY(0px); }
            50%      { transform: translateY(-9px); }
        }

        .welcome-glow-frame {
            position: relative;
            width: 100%;
            border-radius: 30px;
            padding: 3px;
            overflow: hidden;
            will-change: transform;
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.25s ease;
            box-shadow:
                0 8px 0 #261266,
                0 32px 75px rgba(0, 0, 0, 0.6),
                0 0 42px rgba(91, 53, 229, 0.4);
        }

        .welcome-glow-frame::before {
            content: '';
            position: absolute;
            width: 160%;
            height: 160%;
            top: -30%;
            left: -30%;
            background: conic-gradient(
                from 0deg,
                #5B35E5 0deg,
                #FFE600 120deg,
                #7C3AED 240deg,
                #5B35E5 360deg
            );
            animation: spinSlow 7s linear infinite;
            z-index: 0;
        }

        .welcome-card {
            position: relative;
            z-index: 1;
            width: 100%;
            text-align: center;
            color: #18142B;
            background: linear-gradient(165deg, #FFFFFF 0%, #FAF8FF 60%, #F2EEFF 100%);
            border-radius: 27px;
            padding: 30px 26px 28px;
            overflow: hidden;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 27px;
            background-image:
                linear-gradient(rgba(91, 53, 229, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(91, 53, 229, 0.04) 1px, transparent 1px);
            background-size: 26px 26px;
            pointer-events: none;
            z-index: 0;
        }

        .welcome-inner {
            position: relative;
            z-index: 2;
        }

        .welcome-inner > * {
            animation: welcomeContentRise 0.65s cubic-bezier(0.16, 1, 0.3, 1) backwards;
        }
        .welcome-inner > *:nth-child(1) { animation-delay: 0.12s; }
        .welcome-inner > *:nth-child(2) { animation-delay: 0.20s; }
        .welcome-inner > *:nth-child(3) { animation-delay: 0.26s; }
        .welcome-inner > *:nth-child(4) { animation-delay: 0.32s; }
        .welcome-inner > *:nth-child(5) { animation-delay: 0.38s; }
        .welcome-inner > *:nth-child(6) { animation-delay: 0.44s; }
        .welcome-inner > *:nth-child(7) { animation-delay: 0.50s; }
        .welcome-inner > *:nth-child(8) { animation-delay: 0.56s; }

        @keyframes welcomeContentRise {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .emblem-orbit {
            width: 76px;
            height: 76px;
            margin: 0 auto 14px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .orbit-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px dashed #5B35E5;
            opacity: 0.45;
            animation: spinSlow 10s linear infinite;
        }

        .orbit-ring-2 {
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 1.5px solid rgba(245, 184, 0, 0.55);
            animation: spinSlow 16s linear infinite reverse;
        }

        .emblem-core {
            width: 56px;
            height: 56px;
            border-radius: 17px;
            background: linear-gradient(135deg, #FFE600, #F5B800);
            color: #140931;
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow:
                0 5px 0 #B58B00,
                0 14px 24px rgba(245, 184, 0, 0.42);
            animation: pulseCore 2.8s ease-in-out infinite;
        }

        @keyframes pulseCore {
            0%, 100% { transform: scale(1); }
            50%      { transform: scale(1.06); }
        }

        .welcome-subtitle-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(91, 53, 229, 0.1);
            border: 1px solid rgba(91, 53, 229, 0.22);
            color: #5B35E5;
            font-size: 10px;
            font-weight: 800;
            padding: 5px 13px;
            border-radius: 50px;
            letter-spacing: 0.6px;
            margin-bottom: 10px;
        }

        .welcome-heading {
            font-size: 12px;
            font-weight: 800;
            color: #5B35E5;
            letter-spacing: 1.8px;
            text-transform: uppercase;
        }

        .welcome-logo {
            font-family: 'Pacifico', cursive;
            font-size: 38px;
            background: linear-gradient(90deg, #140931 0%, #5B35E5 50%, #140931 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 2px 0 10px;
            filter: drop-shadow(0 5px 10px rgba(91, 53, 229, 0.18));
            animation: shineLogo 4s linear infinite;
        }

        @keyframes shineLogo {
            to { background-position: 200% center; }
        }

        .welcome-text {
            font-size: 12.8px;
            color: #524E6E;
            line-height: 1.6;
            max-width: 390px;
            margin: 0 auto 18px;
        }

        .welcome-mini-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 9px;
            margin-bottom: 18px;
        }

        .mini-feat {
            background: #FFFFFF;
            border: 1.5px solid #E6E2F8;
            border-radius: 14px;
            padding: 10px 6px;
            box-shadow: 0 4px 0 #DFD9F7, 0 8px 16px rgba(20, 9, 49, 0.06);
            transition: all 0.2s ease;
        }

        .mini-feat:hover {
            border-color: #5B35E5;
            transform: translateY(-3px);
            box-shadow: 0 6px 0 #C4B5FD, 0 12px 20px rgba(91, 53, 229, 0.15);
        }

        .mini-feat i {
            color: #5B35E5;
            font-size: 14px;
            margin-bottom: 4px;
            display: block;
        }

        .mini-feat span {
            font-size: 10.5px;
            font-weight: 700;
            color: #18142B;
        }

        .voice-indicator {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: #F3EFFF;
            border: 1px solid #DFD7FF;
            color: #4322C4;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11.5px;
            font-weight: 700;
            margin-bottom: 18px;
            box-shadow: 0 3px 0 #E2DAFF;
        }

        .eq-bars {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 13px;
        }

        .eq-bars span {
            width: 3px;
            height: 5px;
            background: #5B35E5;
            border-radius: 3px;
            animation: eqBounce 0.75s ease-in-out infinite;
        }

        .eq-bars span:nth-child(2) { animation-delay: 0.15s; height: 11px; }
        .eq-bars span:nth-child(3) { animation-delay: 0.35s; height: 7px; }
        .eq-bars span:nth-child(4) { animation-delay: 0.1s; height: 13px; }

        @keyframes eqBounce {
            0%, 100% { height: 4px; }
            50%      { height: 13px; }
        }

        .btn-next-portal {
            width: 100%;
            max-width: 260px;
            margin: 0 auto;
            padding: 13px 24px;
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, #FFE600 0%, #F5C400 100%);
            color: #140931;
            font-size: 14.5px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow:
                0 5px 0 #B58B00,
                0 14px 26px rgba(245, 196, 0, 0.42);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-next-portal::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 55%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.65), transparent);
            transform: skewX(-20deg);
            animation: buttonShimmer 2.8s infinite;
        }

        .btn-next-portal:hover {
            transform: translateY(-2px);
            box-shadow:
                0 7px 0 #B58B00,
                0 18px 34px rgba(245, 196, 0, 0.55);
        }

        .btn-next-portal:active {
            transform: translateY(3px);
            box-shadow:
                0 1px 0 #B58B00,
                0 6px 14px rgba(245, 196, 0, 0.35);
        }

        .btn-next-portal i {
            transition: transform 0.3s ease;
        }

        .btn-next-portal:hover i {
            transform: translateX(5px);
        }

        /* =====================================================
           2. STAGE & KARTU LOGIN 3D INTERAKTIF (TERKUNCI SEJAJAR)
           ===================================================== */
        .login-stage {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 830px;
            opacity: 0;
            transform: perspective(1200px) rotateX(10deg) translateY(50px) scale(0.92);
            pointer-events: none;
        }

        .login-stage.portal-active {
            opacity: 1;
            pointer-events: auto;
            animation: portalReveal 0.85s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes portalReveal {
            0% {
                opacity: 0;
                transform: perspective(1200px) rotateX(12deg) translateY(55px) scale(0.9);
                filter: blur(10px);
            }
            100% {
                opacity: 1;
                transform: perspective(1200px) rotateX(0deg) translateY(0px) scale(1);
                filter: blur(0px);
            }
        }

        .login-float-wrap {
            width: 100%;
            animation: levitateCard 5.5s ease-in-out infinite;
        }

        @keyframes levitateCard {
            0%, 100% { transform: translateY(0px); }
            50%      { transform: translateY(-10px); }
        }

        .login-container {
            position: relative;
            width: 100%;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 26px;
            padding: 12px;
            display: grid;
            grid-template-columns: 1.02fr 1fr;
            align-items: stretch;
            gap: 10px;
            overflow: hidden;
            will-change: transform;
            box-shadow:
                0 8px 0 #D0C8F2,
                0 32px 75px rgba(4, 1, 15, 0.72),
                0 0 0 1px rgba(255, 255, 255, 0.55),
                0 0 0 7px rgba(255, 255, 255, 0.08);
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s ease;
        }

        .login-container:hover {
            box-shadow:
                0 10px 0 #C4B5FD,
                0 38px 85px rgba(91, 53, 229, 0.45),
                0 0 0 1.5px rgba(255, 230, 0, 0.55),
                0 0 0 8px rgba(255, 255, 255, 0.12);
        }

        .brand-panel {
            background: linear-gradient(155deg, #1E0F4A 0%, #12072E 52%, #2B146B 100%);
            border-radius: 20px;
            padding: 28px 26px;
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            height: 100%;
            box-shadow:
                0 4px 0 #09031C,
                inset 0 1px 0 rgba(255, 255, 255, 0.16);
        }

        .panel-grid-overlay {
            position: absolute;
            inset: 0;
            border-radius: 20px;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 28px 28px;
            animation: panGrid 18s linear infinite;
            pointer-events: none;
        }

        @keyframes panGrid {
            from { background-position: 0 0; }
            to   { background-position: 28px 28px; }
        }

        .panel-glow-1 {
            position: absolute;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(91, 53, 229, 0.65) 0%, transparent 70%);
            bottom: -30px;
            right: -20px;
            border-radius: 50%;
            animation: pulseGlow 5s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes pulseGlow {
            0%, 100% { opacity: 0.65; transform: scale(1); }
            50%      { opacity: 1; transform: scale(1.2); }
        }

        .pill-badge {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255, 230, 0, 0.14);
            border: 1px solid rgba(255, 230, 0, 0.35);
            color: #FFE600;
            font-size: 10px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 50px;
            width: fit-content;
            letter-spacing: 0.5px;
        }

        .live-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #4ADE80;
            box-shadow: 0 0 8px #4ADE80;
            animation: blinkDot 1.6s infinite;
        }

        @keyframes blinkDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.35; transform: scale(0.8); }
        }

        .brand-title {
            position: relative;
            z-index: 1;
            font-family: 'Pacifico', cursive;
            font-size: 30px;
            color: #FFFFFF;
            margin-top: 14px;
            letter-spacing: 0.5px;
            text-shadow: 0 6px 24px rgba(91, 53, 229, 0.55);
        }

        .brand-desc {
            position: relative;
            z-index: 1;
            font-size: 12.5px;
            color: #CCC6E8;
            line-height: 1.6;
            margin-top: 8px;
        }

        .feature-list {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 11px;
            position: relative;
            z-index: 1;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            padding: 10px 12px;
            border-radius: 14px;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 0 rgba(7, 3, 20, 0.55);
            transition: all 0.22s ease;
        }

        .login-stage.portal-active .feature-item {
            animation: slideInLeft 0.65s ease backwards;
        }
        .login-stage.portal-active .feature-item:nth-child(1) { animation-delay: 0.2s; }
        .login-stage.portal-active .feature-item:nth-child(2) { animation-delay: 0.35s; }
        .login-stage.portal-active .feature-item:nth-child(3) { animation-delay: 0.5s; }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-22px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .feature-item:hover {
            transform: translateX(5px) translateY(-2px);
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 230, 0, 0.45);
        }

        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #FFE600, #F5C800);
            color: #140931;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 3px 0 #B58B00;
        }

        .feature-item strong { display: block; font-size: 12px; color: #FFFFFF; }
        .feature-item span   { font-size: 10.8px; color: #B9B2D9; }

        .panel-footer {
            position: relative;
            z-index: 1;
            font-size: 11px;
            color: #968EBC;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .replay-intro-link {
            color: #FFE600;
            background: none;
            border: none;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: underline;
        }

        .form-panel {
            padding: 28px 26px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            border-radius: 20px;
            background: linear-gradient(145deg, #FFFFFF 0%, #F8F6FF 55%, #EFEAFF 100%);
            height: 100%;
            overflow: hidden;
        }

        .form-header, .login-form, .quick-role-box {
            position: relative;
            z-index: 1;
        }

        .login-stage.portal-active .form-header {
            animation: fadeUpItem 0.6s ease 0.2s backwards;
        }
        .login-stage.portal-active .login-form {
            animation: fadeUpItem 0.6s ease 0.35s backwards;
        }
        .login-stage.portal-active .quick-role-box {
            animation: fadeUpItem 0.6s ease 0.5s backwards;
        }

        @keyframes fadeUpItem {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .welcome-tag {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            color: #5B35E5;
            background: rgba(91, 53, 229, 0.1);
            padding: 4px 10px;
            border-radius: 7px;
            margin-bottom: 6px;
            letter-spacing: 0.4px;
        }

        .form-header h2 { font-size: 21px; font-weight: 800; color: #18142B; }
        .form-header p  { font-size: 12px; color: #747091; margin-top: 4px; }

        .alert-error {
            position: relative;
            z-index: 1;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #B91C1C;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .login-form { margin-top: 16px; }
        .input-group { margin-bottom: 13px; }
        .input-group label { display: block; font-size: 12px; font-weight: 700; color: #18142B; margin-bottom: 5px; }

        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i.left-icon { position: absolute; left: 14px; color: #8E8CA7; font-size: 13px; transition: 0.25s; }

        .input-wrapper input {
            width: 100%;
            padding: 11px 40px;
            border: 1.5px solid #E2DFF0;
            border-radius: 12px;
            font-size: 13px;
            color: #18142B;
            background: #FFFFFF;
            outline: none;
            box-shadow: 0 3px 0 #ECE9F8;
            transition: all 0.22s ease;
        }

        .input-wrapper input:focus {
            border-color: #5B35E5;
            box-shadow: 0 3px 0 #C4B5FD, 0 0 0 3px rgba(91, 53, 229, 0.1);
        }

        .input-wrapper:focus-within i.left-icon { color: #5B35E5; }

        .toggle-pass {
            position: absolute;
            right: 13px;
            background: none;
            border: none;
            color: #8E8CA7;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-submit {
            position: relative;
            overflow: hidden;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 13px;
            background: linear-gradient(135deg, #6842F5 0%, #4821D4 100%);
            color: #FFFFFF;
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow:
                0 5px 0 #2E119C,
                0 12px 24px rgba(91, 53, 229, 0.34);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            margin-top: 8px;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.32), transparent);
            transform: skewX(-20deg);
            animation: buttonShimmer 3.5s infinite;
        }

        @keyframes buttonShimmer {
            0%   { left: -100%; }
            45%  { left: 150%; }
            100% { left: 150%; }
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow:
                0 7px 0 #2E119C,
                0 16px 30px rgba(91, 53, 229, 0.44);
        }

        .btn-submit:active {
            transform: translateY(3px);
            box-shadow:
                0 1px 0 #2E119C,
                0 4px 10px rgba(91, 53, 229, 0.25);
        }

        .quick-role-box {
            margin-top: 16px;
            padding-top: 13px;
            border-top: 1px dashed #DCD7F0;
        }

        .quick-role-title {
            font-size: 10.5px;
            font-weight: 700;
            color: #7D7A99;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .role-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 9px;
        }

        .role-card-btn {
            background: #FFFFFF;
            border: 1.5px solid #E4E0F5;
            border-radius: 13px;
            padding: 9px 10px;
            text-align: left;
            cursor: pointer;
            box-shadow: 0 4px 0 #DFD9F7, 0 6px 14px rgba(91, 53, 229, 0.07);
            transition: all 0.18s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .role-card-btn:hover {
            border-color: #5B35E5;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #C4B5FD, 0 10px 18px rgba(91, 53, 229, 0.14);
        }

        .role-card-btn:active {
            transform: translateY(2px);
            box-shadow: 0 1px 0 #C4B5FD;
        }

        .role-card-btn.selected-role {
            border-color: #5B35E5;
            background: #F4F0FF;
            box-shadow: 0 4px 0 #5B35E5, 0 8px 18px rgba(91, 53, 229, 0.16);
        }

        .role-badge-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .role-owner .role-badge-icon {
            background: #FFE600;
            color: #140931;
            box-shadow: 0 2px 0 #B58B00;
        }
        .role-kasir .role-badge-icon {
            background: #5B35E5;
            color: #FFFFFF;
            box-shadow: 0 2px 0 #2E119C;
        }

        .role-card-btn strong { display: block; font-size: 11.5px; color: #18142B; }
        .role-card-btn small  { font-size: 9.8px; color: #7D7A99; }

        @media (max-width: 780px) {
            .login-container { grid-template-columns: 1fr; max-width: 420px; }
            .feature-list { display: none; }
            .brand-panel, .form-panel { padding: 24px 20px; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="glass-ring-3d ring-left-3d"></div>
    <div class="glass-ring-3d ring-right-3d"></div>

    <!-- =====================================================
         1. LAYAR SAMBUTAN 3D & LATAR PUTIH ELEGAN
         ===================================================== -->
    <div class="welcome-overlay" id="welcomeScreen">
        <div class="welcome-float-wrap">
            <div class="welcome-glow-frame tilt-3d" id="welcomeTiltCard">
                <div class="card-glare"></div>
                <div class="welcome-card">
                    <div class="welcome-inner">
                        <div class="emblem-orbit">
                            <div class="orbit-ring"></div>
                            <div class="orbit-ring-2"></div>
                            <div class="emblem-core">
                                <i class="fa-solid fa-store"></i>
                            </div>
                        </div>

                        <div class="welcome-subtitle-pill">
                            <span class="live-dot"></span> SISTEM TERINTEGRASI UMKM
                        </div>

                        <div class="welcome-heading">Selamat Datang di</div>
                        <div class="welcome-logo">UMKM Connect</div>
                        <p class="welcome-text">
                            <strong>UMKM Connect</strong> adalah platform digital untuk mengelola transaksi layanan jasa, loyalitas poin membership pelanggan, serta pengiriman nota digital dan promo email otomatis.
                        </p>

                        <div class="welcome-mini-features">
                            <div class="mini-feat">
                                <i class="fa-solid fa-cash-register"></i>
                                <span>Kasir Layanan</span>
                            </div>
                            <div class="mini-feat">
                                <i class="fa-solid fa-award"></i>
                                <span>Poin Membership</span>
                            </div>
                            <div class="mini-feat">
                                <i class="fa-solid fa-envelope"></i>
                                <span>Broadcast Email</span>
                            </div>
                        </div>

                        <div>
                            <div class="voice-indicator">
                                <div class="eq-bars">
                                    <span></span><span></span><span></span><span></span>
                                </div>
                                <span id="statusSuaraText">Asisten Suara Sambutan</span>
                            </div>
                        </div>

                        <button type="button" class="btn-next-portal" onclick="lanjutKeLogin()">
                            <span>Next</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         2. HALAMAN LOGIN 3D (KARTU MELAYANG + TILT INTERAKTIF)
         ===================================================== -->
    <div class="login-stage" id="loginStage">
        <div class="login-float-wrap">
            <div class="login-container tilt-3d" id="loginTiltCard">
                <div class="card-glare"></div>

                <div class="brand-panel">
                    <div class="panel-grid-overlay"></div>
                    <div class="panel-glow-1"></div>

                    <div>
                        <div class="pill-badge">
                            <span class="live-dot"></span> PLATFORM CRM & MEMBERSHIP
                        </div>
                        <div class="brand-title">UMKM Connect</div>
                        <p class="brand-desc">
                            Kelola transaksi layanan jasa, loyalitas poin pelanggan, nota digital WA, dan broadcast promo email dalam satu sistem terintegrasi.
                        </p>

                        <div class="feature-list">
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fa-solid fa-cash-register"></i></div>
                                <div>
                                    <strong>Kasir Layanan Terpadu</strong>
                                    <span>Catat pesanan jasa & hitung poin otomatis.</span>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
                                <div>
                                    <strong>Nota Digital & Broadcast Email</strong>
                                    <span>Kirim struk & promo langsung ke pelanggan.</span>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                                <div>
                                    <strong>Kontrol Analitik Owner</strong>
                                    <span>Pantau omzet & performa member real-time.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-footer">
                        <span>© 2026 UMKM Connect</span>
                        <button type="button" class="replay-intro-link" onclick="ulangSambutan()">
                            <i class="fa-solid fa-rotate-left"></i> Sambutan
                        </button>
                    </div>
                </div>

                <div class="form-panel">
                    <div class="form-header">
                        <span class="welcome-tag">PORTAL INTERNAL</span>
                        <h2>Selamat Datang Kembali</h2>
                        <p>Silakan masuk menggunakan akun Owner atau Admin Anda</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert-error">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span><?= $error ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="login-form">
                        <div class="input-group">
                            <label for="email">Email Staff / Owner</label>
                            <div class="input-wrapper">
                                <input type="email" id="email" name="email" placeholder="nama@umkm.com" required>
                                <i class="fa-solid fa-envelope left-icon"></i>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                                <i class="fa-solid fa-lock left-icon"></i>
                                <button type="button" class="toggle-pass" onclick="togglePassword()" title="Lihat/Sembunyikan Password">
                                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <span>Masuk ke Sistem</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>

                    <div class="quick-role-box">
                        <span class="quick-role-title">Isi Otomatis Akun Sistem (Klik Role):</span>
                        <div class="role-grid">
                            <button type="button" id="btnOwner" class="role-card-btn role-owner" onclick="isiAkun('owner@umkm.com', 'password', 'btnOwner')">
                                <div class="role-badge-icon"><i class="fa-solid fa-crown"></i></div>
                                <div>
                                    <strong>Superadmin</strong>
                                    <small>Owner • owner@umkm.com</small>
                                </div>
                            </button>

                            <button type="button" id="btnKasir" class="role-card-btn role-kasir" onclick="isiAkun('kasir@umkm.com', 'password', 'btnKasir')">
                                <div class="role-badge-icon"><i class="fa-solid fa-headset"></i></div>
                                <div>
                                    <strong>Admin Kasir</strong>
                                    <small>Kasir • kasir@umkm.com</small>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const adaErrorLogin = <?= $error ? 'true' : 'false' ?>;
        let suaraSudahJalan = false;
        let sudahMasukLogin = false;

        function putarSuaraLangsung() {
            if (suaraSudahJalan || sudahMasukLogin) return;
            if (!('speechSynthesis' in window)) return;

            const teksSambutan =
                "Selamat datang di UMKM Connect " +
                "platform kasir layanan dan poin membership serta broadcast promo email untuk bisnis UMKM Anda " +
                "silakan tekan tombol Next untuk masuk ke halaman login";

            const ucapan = new SpeechSynthesisUtterance(teksSambutan);
            ucapan.lang = 'id-ID';
            ucapan.rate = 1.08;
            ucapan.pitch = 1.04;

            const voices = window.speechSynthesis.getVoices();
            const indoLocal = voices.find(v => v.lang.toLowerCase().includes('id') && v.localService);
            const indoAny   = voices.find(v => v.lang.toLowerCase().includes('id'));
            if (indoLocal) {
                ucapan.voice = indoLocal;
            } else if (indoAny) {
                ucapan.voice = indoAny;
            }

            const statusEl = document.getElementById('statusSuaraText');

            ucapan.onstart = () => {
                suaraSudahJalan = true;
                if (statusEl) statusEl.innerText = "Sedang Menjelaskan Website...";
            };

            ucapan.onend = () => {
                if (statusEl) statusEl.innerText = "Silakan Tekan Tombol Next";
            };

            ucapan.onerror = () => {
                suaraSudahJalan = false;
            };

            window.speechSynthesis.speak(ucapan);
        }

        if (adaErrorLogin) {
            sudahMasukLogin = true;
            document.getElementById('welcomeScreen').classList.add('hidden-screen');
            document.getElementById('loginStage').classList.add('portal-active');
        } else {
            putarSuaraLangsung();

            if ('speechSynthesis' in window) {
                window.speechSynthesis.onvoiceschanged = () => {
                    if (!suaraSudahJalan && !sudahMasukLogin) {
                        putarSuaraLangsung();
                    }
                };
            }

            ['pointerdown', 'keydown', 'touchstart'].forEach(evt => {
                window.addEventListener(evt, () => {
                    if (!suaraSudahJalan && !sudahMasukLogin) {
                        putarSuaraLangsung();
                    }
                }, { once: true });
            });
        }

        // Efek 3D Tilt & Pantulan Cahaya pada Kartu Sambutan & Kartu Login
        document.querySelectorAll('.tilt-3d').forEach(card => {
            const glare = card.querySelector('.card-glare');
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const maxTilt = card.id === 'welcomeTiltCard' ? 7 : 4.5;
                const rotateX = ((y - centerY) / centerY) * -maxTilt;
                const rotateY = ((x - centerX) / centerX) * maxTilt;

                card.style.transform = `perspective(1100px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                if (glare) {
                    glare.style.setProperty('--gx', `${(x / rect.width) * 100}%`);
                    glare.style.setProperty('--gy', `${(y / rect.height) * 100}%`);
                }
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });

        function lanjutKeLogin() {
            sudahMasukLogin = true;

            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }

            const welcomeEl = document.getElementById('welcomeScreen');
            const loginEl   = document.getElementById('loginStage');

            welcomeEl.classList.add('exit-portal');

            setTimeout(() => {
                welcomeEl.classList.add('hidden-screen');
                loginEl.classList.add('portal-active');
            }, 400);
        }

        function ulangSambutan() {
            sudahMasukLogin = false;
            suaraSudahJalan = false;
            const welcomeEl = document.getElementById('welcomeScreen');
            const loginEl   = document.getElementById('loginStage');
            loginEl.classList.remove('portal-active');
            welcomeEl.classList.remove('hidden-screen', 'exit-portal');
            putarSuaraLangsung();
        }

        function togglePassword() {
            const passInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passInput.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function isiAkun(email, pass, btnId) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
            document.querySelectorAll('.role-card-btn').forEach(btn => btn.classList.remove('selected-role'));
            document.getElementById(btnId).classList.add('selected-role');
        }
    </script>
</body>
</html>