<?php
// logout.php

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simpan role dan username sebelum session dihancurkan
$role = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? null;

// Debugging (opsional)
error_log("User  $username dengan role $role melakukan logout");

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman utama
$redirectUrl = '/CODINGAN/1-pagebeforelogin/1-beforelogin.php?status=logout_sukses';

if (!headers_sent()) {
    header("Location: $redirectUrl");
    exit;
} else {
    echo "<script>window.location.href='$redirectUrl';</script>";
    exit;
}
?>
