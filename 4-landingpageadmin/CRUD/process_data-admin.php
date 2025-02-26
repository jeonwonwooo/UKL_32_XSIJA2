<?php
include '../../formkoneksi.php';

// Ambil parameter 'action' dan 'id' dari URL
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'delete' && $id) {
    try {
        // Query untuk menghapus data admin berdasarkan ID
        $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
        $stmt->execute([$id]);

        // Redirect ke halaman daftar admin setelah berhasil menghapus
        header("Location: data-admin_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    // Jika action atau ID tidak valid, kembalikan ke halaman daftar admin
    header("Location: data-admin_list.php");
    exit;
}
?>