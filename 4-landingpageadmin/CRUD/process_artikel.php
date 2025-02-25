<?php
include '../../formkoneksi.php';

// Ambil action dan ID dari URL
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'publish' && $id) {
    // Proses Publikasikan Artikel
    try {
        $stmt = $conn->prepare("UPDATE artikel SET status = 'published' WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: artikel_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} elseif ($action === 'delete' && $id) {
    // Proses Hapus Artikel
    try {
        // Ambil nama gambar sebelum dihapus
        $stmt = $conn->prepare("SELECT gambar FROM artikel WHERE id = ?");
        $stmt->execute([$id]);
        $artikel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($artikel) {
            $upload_dir = "../../uploads/";
            if ($artikel['gambar'] && file_exists($upload_dir . $artikel['gambar'])) {
                unlink($upload_dir . $artikel['gambar']); // Hapus file gambar
            }

            // Hapus artikel dari database
            $stmt = $conn->prepare("DELETE FROM artikel WHERE id = ?");
            $stmt->execute([$id]);
        }

        header("Location: artikel_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    // Jika action tidak valid
    die("Invalid action.");
}
?>