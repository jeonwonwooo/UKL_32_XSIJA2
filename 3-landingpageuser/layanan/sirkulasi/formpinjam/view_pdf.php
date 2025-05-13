<?php
session_start();

// Cek login dulu
if (!isset($_SESSION['user_id'])) {
    die('<h2 style="color:red">Akses ditolak. Silakan login terlebih dahulu.</h2>');
}

// Ambil nama file dari URL
$file = rawurldecode($_GET['file'] ?? '');
$file = basename($file);
$file = preg_replace('/[^\w\s\-\.]/u', '', $file); // Hapus karakter ilegal
$file = trim($file);

// Path file PDF
$base_dir = $_SERVER['DOCUMENT_ROOT'] . '/CODINGAN/4-landingpageadmin/uploads/';
$file_path = $base_dir . $file;

// Debugging (hapus setelah fix)
echo "Debug: Base Directory: " . htmlspecialchars($base_dir) . "<br>";
echo "Debug: File Name: " . htmlspecialchars($file) . "<br>";
echo "Debug: Full Path: " . htmlspecialchars($file_path) . "<br>";

// Cek apakah file ada
if (!file_exists($file_path)) {
    die('<h2 style="color:red">File tidak ditemukan. Pastikan nama file benar!</h2>');
}

// Redirect ke file asli (tanpa PHP intervensi)
header("Location: /CODINGAN/4-landingpageadmin/uploads/" . rawurlencode($file));
exit;
?>