<?php

// Koneksi database
require_once '../formkoneksi.php';

try {
    session_start();
    $user_id = $_SESSION['user_id'] ?? 0;

    if (!$user_id) {
        throw new Exception("Anda harus login terlebih dahulu.");
    }

    $dokumen_id = $_POST['dokumen_id'] ?? 0;

    if (!$dokumen_id) {
        throw new Exception("ID dokumen tidak valid.");
    }

    // Cek apakah user ada di tabel anggota
    $check_user_query = "SELECT * FROM anggota WHERE id = :user_id";
    $check_user_stmt = $conn->prepare($check_user_query);
    $check_user_stmt->bindParam(':user_id', $user_id);
    $check_user_stmt->execute();

    if (!$check_user_stmt->rowCount()) {
        throw new Exception("Error: User ID tidak ditemukan di tabel anggota.");
    }

    // Cek apakah dokumen ada di tabel dokumen
    $check_dokumen_query = "SELECT * FROM dokumen WHERE id = :dokumen_id";
    $check_dokumen_stmt = $conn->prepare($check_dokumen_query);
    $check_dokumen_stmt->bindParam(':dokumen_id', $dokumen_id);
    $check_dokumen_stmt->execute();

    if (!$check_dokumen_stmt->rowCount()) {
        throw new Exception("Error: Dokumen ID tidak ditemukan di tabel dokumen.");
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        // Cek apakah sudah ada di favorit
        $check_query = "SELECT * FROM favorit_dokumen WHERE dokumen_id = :dokumen_id AND user_id = :user_id";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':dokumen_id', $dokumen_id);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->execute();

        if (!$check_stmt->rowCount()) {
            // Tambahkan ke favorit dokumen
            $insert_query = "INSERT INTO favorit_dokumen (user_id, dokumen_id) VALUES (:user_id, :dokumen_id)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bindParam(':user_id', $user_id);
            $insert_stmt->bindParam(':dokumen_id', $dokumen_id);
            $insert_stmt->execute();

            header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php?status=success");
            exit();
        } else {
            header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php?status=exists");
            exit();
        }
    } elseif ($action === 'hapus') {
        // Hapus dari favorit
        $delete_query = "DELETE FROM favorit_dokumen WHERE dokumen_id = :dokumen_id AND user_id = :user_id";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bindParam(':dokumen_id', $dokumen_id);
        $delete_stmt->bindParam(':user_id', $user_id);
        $delete_stmt->execute();

        header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php?status=removed");
        exit();
    } else {
        throw new Exception("Aksi tidak valid.");
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}