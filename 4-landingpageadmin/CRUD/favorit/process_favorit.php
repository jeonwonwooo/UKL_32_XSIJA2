<?php
include 'formkoneksi.php';

// Get favorite ID from URL
$id = $_GET['id'] ?? '';

if (empty($id)) {
    die("ID favorit tidak valid.");
}

try {
    // Delete favorite entry
    $stmt = $conn->prepare("DELETE FROM favorit WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: favorit_list.php");
    exit;
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>