<?php
include 'formkoneksi.php';

// Ambil ID denda dari URL
$denda_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$denda_id) {
    die("ID DENDA TIDAK VALID");
}

try {
    // Update status_pembayaran menjadi 'success' dan status menjadi 'sudah_dibayar'
    $query = "UPDATE denda SET status_pembayaran = 'success', status = 'sudah_dibayar' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $denda_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: denda_list.php?status=diterima");
        exit;
    } else {
        die("Gagal memperbarui status pembayaran.");
    }
} catch (Exception $e) {
    die("ERROR: " . $e->getMessage());
}
?>