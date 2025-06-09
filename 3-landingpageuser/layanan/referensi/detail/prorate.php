<?php
session_start();
require_once 'formkoneksi.php';

$action = $_POST['action'];
$dokumen_id = $_POST['dokumen_id'];
$nilai = $_POST['nilai'];
$ulasan = $_POST['ulasan'];
$user_id = $_SESSION['user_id'];

// Validasi input
if (empty($nilai) || empty($ulasan)) {
    header("Location: tambahulrate.php?id=$dokumen_id&error=Please fill all fields");
    exit;
}

switch ($action) {
    case 'create':
        // Tambah rating baru
        $insert_query = "INSERT INTO rating (anggota_id, dokumen_id, nilai, ulasan) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->execute([$user_id, $dokumen_id, $nilai, $ulasan]);

        header("Location: /CODINGAN/3-landingpageuser/layanan/referensi/detail/detail_dokumen.php?id=$dokumen_id&success=Rating berhasil ditambahkan");
        break;

    case 'update':
        // Edit rating yang sudah ada
        $rating_id = $_POST['rating_id'];
        $update_query = "UPDATE rating SET nilai = ?, ulasan = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->execute([$nilai, $ulasan, $rating_id]);

        header("Location: /CODINGAN/3-landingpageuser/layanan/referensi/detail/detail_dokumen.php?id=$dokumen_id&success=Rating berhasil diperbarui");
        break;

    case 'delete':
        // Hapus rating
        $rating_id = $_POST['rating_id'];
        $delete_query = "DELETE FROM rating WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->execute([$rating_id]);

        header("Location: /CODINGAN/3-landingpageuser/layanan/referensi/detail/detail_dokumen.php?id=$dokumen_id&success=Rating berhasil dihapus");
        break;

    default:
        header("Location: /CODINGAN/3-landingpageuser/layanan/referensi/detail/detail_dokumen.php?id=$dokumen_id&error=Invalid action");
        break;
}
?>