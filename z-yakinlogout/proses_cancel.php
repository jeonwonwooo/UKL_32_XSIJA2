<?php
// batal_logout.php

// Pastikan tidak ada output sebelum session_start()
ob_start();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'formkoneksi.php';

// Ambil data session dengan sanitasi
$username = trim($_SESSION['username'] ?? '');
$role = $_SESSION['role'] ?? '';

// Debugging detail
error_log("========== ADMIN REDIRECT PROCESS ==========");
error_log("Username: " . ($username ?: '[kosong]'));
error_log("Role awal: " . ($role ?: '[belum di-set]'));

// Jika username kosong
if (empty($username)) {
    error_log("ERROR: Username session kosong");
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=error_username_kosong");
    exit;
}

try {
    // PROSES VERIFIKASI ADMIN
    error_log("Mengecek tabel admin...");
    $stmtAdmin = $conn->prepare("SELECT username FROM admin WHERE BINARY username = ? LIMIT 1");
    $stmtAdmin->execute([$username]);
    $adminData = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

    if ($adminData) {
        // UPDATE SESSION ROLE
        $_SESSION['role'] = 'admin';
        error_log("Role diperbarui ke admin");
        
        // BUILD REDIRECT URL
        $redirectUrl = '/CODINGAN/4-landingpageadmin/landingpage/dashboard.php?status=';
        
        // CEK ERROR LEVEL
        if ($stmtAdmin->rowCount() > 0) {
            $redirectUrl .= 'success_admin';
            error_log("Redirect sukses ke dashboard admin");
        } else {
            $redirectUrl .= 'error_admin_not_found';
            error_log("ERROR: Data admin tidak ditemukan");
        }

        // PROSES REDIRECT
        if (!headers_sent()) {
            header("Location: $redirectUrl");
            exit;
        } else {
            error_log("WARNING: Headers sudah terkirim, menggunakan JS redirect");
            echo "<script>
                console.error('Admin Redirect Error:');
                window.location.href = '$redirectUrl';
            </script>";
            exit;
        }
    }

    // FALLBACK KE USER JIKA BUKAN ADMIN
    error_log("User bukan admin, mengecek tabel anggota...");
    $stmtAnggota = $conn->prepare("SELECT username FROM anggota WHERE BINARY username = ? LIMIT 1");
    $stmtAnggota->execute([$username]);
    
    if ($stmtAnggota->fetch()) {
        $_SESSION['role'] = 'user';
        header("Location: /CODINGAN/3-landingpageuser/beranda/beranda.php?status=error_fallback_user");
        exit;
    }

    // JIKA TIDAK DIKENAL
    error_log("ERROR: User tidak terdaftar");
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=error_unregistered_user");
    exit;

} catch (PDOException $e) {
    error_log("DATABASE ERROR: " . $e->getMessage());
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=error_db_connection");
    exit;
} finally {
    ob_end_flush(); // Kirim output buffer
}