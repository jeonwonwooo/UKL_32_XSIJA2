<?php
include 'formkoneksi.php';

// Ambil id peminjaman dari parameter GET
$id = $_GET['id'] ?? '';

// Validasi input
if (empty($id)) {
    die("ID peminjaman tidak valid.");
}

try {
    // Hapus data peminjaman
    $delete_query = "DELETE FROM peminjaman WHERE id = ?";
    $stmt_delete = $conn->prepare($delete_query);
    $stmt_delete->bind_param("i", $id);
    $stmt_delete->execute();

    echo "Data peminjaman berhasil dihapus!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>