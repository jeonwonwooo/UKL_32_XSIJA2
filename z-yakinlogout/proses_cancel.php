<?php
// proses_cancel.php

// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'formkoneksi.php';

// Ambil dan bersihkan username dari session
$username = trim($_SESSION['username'] ?? '');
$role = $_SESSION['role'] ?? '';

// Debugging
error_log("========== [BATAL LOGOUT] ==========");
error_log("Username dari session: '$username'");
error_log("Role dari session: '$role'");

// Jika username kosong, langsung redirect ke login
if (empty($username)) {
    error_log("Username kosong saat batal logout");
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=username_kosong");
    exit;
}

try {
    // Cek apakah username ada di tabel admin
    $stmtAdmin = $conn->prepare("SELECT username FROM admin WHERE username = ?");
    $stmtAdmin->execute([$username]);
    $isAdmin = $stmtAdmin->fetch();

    if ($isAdmin) {
        // Update role jika perlu
        if ($role !== 'admin') {
            $_SESSION['role'] = 'admin';
        }

        // Redirect ke dashboard admin
        $redirectUrl = '/CODINGAN/4-landingpageadmin/landingpage/dashboard.php?status=balik_admin';
        error_log("Mengarahkan ke: $redirectUrl");

        if (!headers_sent()) {
            header("Location: $redirectUrl");
            exit;
        } else {
            die("Headers sudah terkirim. Redirect gagal.");
        }
    }

    // Jika bukan admin, cek apakah anggota
    $stmtAnggota = $conn->prepare("SELECT username FROM anggota WHERE username = ?");
    $stmtAnggota->execute([$username]);
    $isUser = $stmtAnggota->fetch();

    if ($isUser) {
        // Update role jika perlu
        if ($role !== 'user') {
            $_SESSION['role'] = 'user';
        }

        // Redirect ke landing page user
        $redirectUrl = '/CODINGAN/3-landingpageuser/beranda/beranda.php?status=balik_anggota';
        error_log("Mengarahkan ke: $redirectUrl");

        if (!headers_sent()) {
            header("Location: $redirectUrl");
            exit;
        } else {
            die("Headers sudah terkirim. Redirect gagal.");
        }
    }

    // Jika tidak ditemukan di kedua tabel
    error_log("User tidak dikenal");
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=ga_kenal");
    exit;

} catch (PDOException $e) {
    error_log("ERROR DATABASE: " . $e->getMessage());
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=error_db");
    exit;
}