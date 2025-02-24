<?php
// Koneksi ke database menggunakan PDO
try {
    $host = 'localhost'; // Ganti sesuai konfigurasi
    $db_name = 'perpustakaan'; // Ganti sesuai nama database
    $username = 'root'; // Ganti sesuai username database
    $password = ''; // Ganti sesuai password database

    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
