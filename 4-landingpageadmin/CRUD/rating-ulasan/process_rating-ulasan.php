<?php
include 'formkoneksi.php';

// Get rating ID from URL
$id = $_GET['id'] ?? '';

if (empty($id)) {
    die("ID rating tidak valid.");
}

try {
    // Delete rating
    $stmt = $conn->prepare("DELETE FROM rating WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: rating_list.php");
    exit;
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>