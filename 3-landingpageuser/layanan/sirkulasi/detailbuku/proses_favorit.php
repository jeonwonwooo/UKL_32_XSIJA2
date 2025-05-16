<?php

// Koneksi database
require_once 'formkoneksi.php';

try {
    session_start();
    $user_id = $_SESSION['user_id'] ?? 0;

    if (!$user_id) {
        throw new Exception("Anda harus login terlebih dahulu.");
    }

    $buku_id = $_POST['buku_id'] ?? 0;
    $action = $_POST['action'] ?? '';

    if (!$buku_id) {
        throw new Exception("ID buku tidak valid.");
    }

    $check_user_query = "SELECT * FROM anggota WHERE id = :user_id";
    $check_user_stmt = $conn->prepare($check_user_query);
    $check_user_stmt->bindParam(':user_id', $user_id);
    $check_user_stmt->execute();

    if (!$check_user_stmt->rowCount()) {
        throw new Exception("Error: User ID tidak ditemukan di tabel anggota.");
    }

    $check_buku_query = "SELECT * FROM buku WHERE id = :buku_id";
    $check_buku_stmt = $conn->prepare($check_buku_query);
    $check_buku_stmt->bindParam(':buku_id', $buku_id);
    $check_buku_stmt->execute();

    if (!$check_buku_stmt->rowCount()) {
        throw new Exception("Error: Buku ID tidak ditemukan di tabel buku.");
    }

    if ($action === 'tambah') {
        $check_query = "SELECT * FROM favorit WHERE user_id = :user_id AND buku_id = :buku_id";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':user_id', $user_id);
        $check_stmt->bindParam(':buku_id', $buku_id);
        $check_stmt->execute();

        if (!$check_stmt->rowCount()) {
            $insert_query = "INSERT INTO favorit (user_id, buku_id) VALUES (:user_id, :buku_id)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bindParam(':user_id', $user_id);
            $insert_stmt->bindParam(':buku_id', $buku_id);
            $insert_stmt->execute();

            header("Location: favorit.php?status=success");
            exit();
        } else {
            header("Location: favorit.php?status=exists");
            exit();
        }
    } elseif ($action === 'hapus') {
        $delete_query = "DELETE FROM favorit WHERE user_id = :user_id AND buku_id = :buku_id";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bindParam(':user_id', $user_id);
        $delete_stmt->bindParam(':buku_id', $buku_id);
        $delete_stmt->execute();

        header("Location: favorit.php?status=removed");
        exit();
    } else {
        throw new Exception("Aksi tidak valid.");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}