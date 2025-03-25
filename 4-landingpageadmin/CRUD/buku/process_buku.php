<?php
include 'formkoneksi.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'delete' && $id) {
    try {
        // Ambil data gambar dan file eBook sebelum menghapus buku
        $stmt = $conn->prepare("SELECT gambar, file_path FROM buku WHERE id = ?");
        $stmt->execute([$id]);
        $buku = $stmt->fetch();

        if ($buku) {
            $upload_dir = "../../uploads/";

            // Hapus gambar jika ada
            if (!empty($buku['gambar']) && file_exists($upload_dir . $buku['gambar'])) {
                unlink($upload_dir . $buku['gambar']);
            }

            // Hapus file eBook jika ada
            if (!empty($buku['file_path']) && file_exists("../../" . $buku['file_path'])) {
                unlink("../../" . $buku['file_path']);
            }

            // Hapus data buku dari database
            $stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
            $stmt->execute([$id]);
        }

        header("Location: buku_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid action.");
}
?>