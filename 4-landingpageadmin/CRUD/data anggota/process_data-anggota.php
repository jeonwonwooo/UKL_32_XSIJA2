<?php
include 'formkoneksi.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'delete' && $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM anggota WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: data-anggota_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: data-anggota_list.php");
    exit;
}
