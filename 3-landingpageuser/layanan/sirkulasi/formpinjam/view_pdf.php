<?php
session_start();

// [1] CEK LOGIN
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.0 403 Forbidden');
    die('<h2 style="color:red">Akses ditolak. Silakan login terlebih dahulu.</h2>');
}

// [2] SANITASI INPUT
$file = $_GET['file'] ?? '';
$file = basename($file); // Hapus path traversal
$file = preg_replace('/[^\w\s\-\.()]/u', '', $file); // Hapus karakter khusus
$file = trim($file); // Hilangkan spasi di awal/akhir

// [3] VALIDASI DASAR
if (empty($file)) {
    die('<h2 style="color:red">Nama file tidak boleh kosong</h2>');
}

if (!preg_match('/\.pdf$/i', $file)) {
    die('<h2 style="color:red">Hanya file PDF yang diperbolehkan</h2>');
}

// [4] PATH AMAN - UPDATED TO CORRECT PATH
$base_dir = "C:/xampp/htdocs/CODINGAN/4-landingpageadmin/uploads/";

// Check if directory exists
if (!is_dir($base_dir)) {
    die('<h2 style="color:red">Direktori upload tidak ditemukan. Hubungi administrator.</h2>');
}

$file_path = $base_dir . $file;

// [5] CEK FILE EXISTENCE
if (!file_exists($file_path)) {
    echo "<h3>Debug Info:</h3>";
    echo "Nama file diminta: <strong>" . htmlspecialchars($file) . "</strong><br>";
    echo "Dicari di lokasi: <strong>" . htmlspecialchars($base_dir) . "</strong><br><br>";

    echo "Daftar file yang ada:<ul>";
    $files = scandir($base_dir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            echo "<li>" . htmlspecialchars($f) . "</li>";
        }
    }
    echo "</ul>";

    die('<span style="color:red">File tidak ditemukan. Pastikan nama file benar!</span>');
}

// [6] HEADER PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . htmlspecialchars($file) . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>