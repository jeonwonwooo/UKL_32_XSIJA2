<?php

require_once '../formkoneksi.php';

try {
    session_start();
    $user_id = $_SESSION['user_id'] ?? 0;

    if (!$user_id) {
        throw new Exception("Anda harus login terlebih dahulu.");
    }

    $dokumen_id = $_POST['dokumen_id'] ?? 0;
    $action = $_POST['action'] ?? '';

    if (!$dokumen_id) {
        throw new Exception("ID dokumen tidak valid.");
    }

    // Cek user valid
    $check_user = $conn->prepare("SELECT id FROM anggota WHERE id = :user_id");
    $check_user->bindParam(':user_id', $user_id);
    $check_user->execute();
    if (!$check_user->rowCount()) {
        throw new Exception("User tidak ditemukan.");
    }

    // Cek dokumen valid
    $check_dokumen = $conn->prepare("SELECT id FROM dokumen WHERE id = :dokumen_id");
    $check_dokumen->bindParam(':dokumen_id', $dokumen_id);
    $check_dokumen->execute();
    if (!$check_dokumen->rowCount()) {
        throw new Exception("Dokumen tidak ditemukan.");
    }

    if ($action === 'tambah') {
        $cek = $conn->prepare("SELECT id FROM favorit WHERE user_id = :user_id AND dokumen_id = :dokumen_id");
        $cek->bindParam(':user_id', $user_id);
        $cek->bindParam(':dokumen_id', $dokumen_id);
        $cek->execute();

        if (!$cek->rowCount()) {
            $insert = $conn->prepare("INSERT INTO favorit (user_id, dokumen_id) VALUES (:user_id, :dokumen_id)");
            $insert->bindParam(':user_id', $user_id);
            $insert->bindParam(':dokumen_id', $dokumen_id);
            $insert->execute();
            header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php?status=success");
            exit();
        } else {
            header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php?status=exists");
            exit();
        }

    } elseif ($action === 'hapus') {
        $delete = $conn->prepare("DELETE FROM favorit WHERE user_id = :user_id AND dokumen_id = :dokumen_id");
        $delete->bindParam(':user_id', $user_id);
        $delete->bindParam(':dokumen_id', $dokumen_id);
        $delete->execute();
        header("Location: /CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php?status=removed");
        exit();
    } else {
        throw new Exception("Aksi tidak valid.");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} catch (PDOException $e) {
    die("Error DB: " . $e->getMessage());
}
