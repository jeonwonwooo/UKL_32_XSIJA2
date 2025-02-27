<?php
include 'formkoneksi.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'publish' && $id) {
    try {
        $stmt = $conn->prepare("UPDATE artikel SET status = 'published' WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: artikel_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} elseif ($action === 'delete' && $id) {
    try {
        $stmt = $conn->prepare("SELECT gambar FROM artikel WHERE id = ?");
        $stmt->execute([$id]);
        $artikel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($artikel) {
            $upload_dir = "../../uploads/";
            if ($artikel['gambar'] && file_exists($upload_dir . $artikel['gambar'])) {
                unlink($upload_dir . $artikel['gambar']);
            }
            $stmt = $conn->prepare("DELETE FROM artikel WHERE id = ?");
            $stmt->execute([$id]);
        }

        header("Location: artikel_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid action.");
}
