<?php
// batal_logout.php

// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'formkoneksi.php';

// Ambil dan bersihkan username dari session
$username = trim($_SESSION['username'] ?? '');
$role = $_SESSION['role'] ?? '';

// Jika username kosong, langsung redirect ke login
if (empty($username)) {
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=username_kosong");
    exit;
}

try {
    // Cek apakah username ada di tabel admin
    $stmtAdmin = $conn->prepare("SELECT username FROM admin WHERE BINARY username = ? LIMIT 1");
    $stmtAdmin->execute([$username]);
    $isAdmin = $stmtAdmin->fetch();

    if ($isAdmin) {
        // Update role jika perlu
        if ($role !== 'admin') {
            $_SESSION['role'] = 'admin';
        }

        // Redirect ke dashboard admin
        header("Location: /CODINGAN/4-landingpageadmin/landingpage/dashboard.php?status=balik_admin");
        exit;
    }

    // Jika bukan admin, cek apakah anggota
    $stmtAnggota = $conn->prepare("SELECT username FROM anggota WHERE username = ?");
    $stmtAnggota->execute([$username]);
    $isUser  = $stmtAnggota->fetch();

    if ($isUser ) {
        // Jika ditemukan di tabel anggota
        if ($role !== 'user') {
            $_SESSION['role'] = 'user';
        }

        // Redirect ke beranda anggota
        header("Location: /CODINGAN/3-landingpageuser/beranda/beranda.php?status=balik_anggota");
        exit;
    }

    // Jika tidak ditemukan di kedua tabel
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=ga_kenal");
    exit;

} catch (PDOException $e) {
    error_log("ERROR DATABASE: " . $e->getMessage());
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=error_db");
    exit;
}
