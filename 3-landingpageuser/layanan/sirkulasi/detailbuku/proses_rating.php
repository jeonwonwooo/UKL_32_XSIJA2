<?php
session_start();
require_once 'formkoneksi.php';

$action = $_POST['action'];
$buku_id = $_POST['buku_id'];
$nilai = $_POST['nilai'];
$ulasan = $_POST['ulasan'];
$user_id = $_SESSION['user_id'];

// Validasi input
if (empty($nilai) || empty($ulasan)) {
    header("Location: index.php?id=$buku_id&error=Please fill all fields");
    exit;
}

switch ($action) {
    case 'create':
        // Tambah rating baru
        $insert_query = "INSERT INTO rating (anggota_id, buku_id, nilai, ulasan) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->execute([$user_id, $buku_id, $nilai, $ulasan]);

        header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/detail_buku.php?id=$buku_id&success=Rating berhasil ditambahkan");
        break;

    case 'update':
        // Edit rating yang sudah ada
        $rating_id = $_POST['rating_id'];
        $update_query = "UPDATE rating SET nilai = ?, ulasan = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->execute([$nilai, $ulasan, $rating_id]);

        header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/detail_buku.php?id=$buku_id&success=Rating berhasil diperbarui");
        break;

    case 'delete':
        // Hapus rating
        $rating_id = $_POST['rating_id'];
        $delete_query = "DELETE FROM rating WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->execute([$rating_id]);

        header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/detail_buku.php?id=$buku_id&success=Rating berhasil dihapus");
        break;

    default:
        header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/detail_buku.php?id=$buku_id&error=Invalid action");
        break;
}
?>