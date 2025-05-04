<?php
include 'formkoneksi.php';

// Debugging: Pastikan $conn ada
if (!$conn) {
    die("Koneksi database tidak tersedia.");
}

// Ambil id peminjaman dari parameter GET
$id = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

// Validasi input
if (empty($id) || empty($action)) {
    die("Parameter tidak valid.");
}

try {
    if ($action === 'kembalikan') {
        // Update status peminjaman menjadi 'dikembalikan'
        $update_query = "
            UPDATE peminjaman
            SET status = 'dikembalikan', akses_berakhir = CURDATE()
            WHERE id = ?
        ";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("i", $id);
        $stmt_update->execute();

        // Update status buku menjadi 'tersedia'
        $select_query = "SELECT buku_id FROM peminjaman WHERE id = ?";
        $stmt_select = $conn->prepare($select_query);
        $stmt_select->bind_param("i", $id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $row = $result->fetch_assoc();

        $buku_id = $row['buku_id'];

        $update_buku_query = "UPDATE buku SET status = 'tersedia' WHERE id = ?";
        $stmt_update_buku = $conn->prepare($update_buku_query);
        $stmt_update_buku->bind_param("i", $buku_id);
        $stmt_update_buku->execute();

        echo "Buku berhasil dikembalikan!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
