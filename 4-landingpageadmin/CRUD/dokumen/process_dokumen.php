<?php
include 'formkoneksi.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'delete' && $id) {
    try {
        // Ambil data file_path sebelum menghapus dokumen
        $stmt = $conn->prepare("SELECT file_path FROM dokumen WHERE id = ?");
        $stmt->execute([$id]);
        $dokumen = $stmt->fetch();

        if ($dokumen) {
            $upload_dir = "../../uploads/";

            // Hapus file dokumen jika ada
            if (!empty($dokumen['file_path']) && file_exists($upload_dir . $dokumen['file_path'])) {
                unlink($upload_dir . $dokumen['file_path']);
            }

            // Hapus data dokumen dari database
            $stmt = $conn->prepare("DELETE FROM dokumen WHERE id = ?");
            $stmt->execute([$id]);
        }

        header("Location: dokumen_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid action.");
}
?>