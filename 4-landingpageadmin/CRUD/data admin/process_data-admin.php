<?php
include 'formkoneksi.php';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'delete' && $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: data-admin_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: data-admin_list.php");
    exit;
}
