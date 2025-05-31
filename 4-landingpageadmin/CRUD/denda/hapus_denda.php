<?php
include 'formkoneksi.php';

session_start();

// Pastikan pengguna sudah login sebagai admin
if (!isset($_SESSION['admin_id'])) {
    die("HARUS LOGIN DULU SEBAGAI ADMIN!");
}

// Ambil ID denda dari GET
$denda_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$denda_id) {
    die("ID DENDA TIDAK VALID");
}

try {
    // Mulai transaksi
    $conn->beginTransaction();

    // Cek apakah denda ada
    $query = "SELECT * FROM denda WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$denda_id]);
    $denda = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$denda) {
        throw new Exception("Data denda tidak ditemukan.");
    }

    // Hapus denda
    $delete = $conn->prepare("DELETE FROM denda WHERE id = ?");
    $delete->execute([$denda_id]);

    // Commit transaksi
    $conn->commit();

    $_SESSION['success_message'] = "Denda berhasil dihapus.";
    header("Location: denda_list.php");
    exit;
} catch (Exception $e) {
    // Rollback jika terjadi error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    die("ERROR: " . $e->getMessage());
}