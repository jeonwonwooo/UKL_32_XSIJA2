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
        $stmt_update->bindValue(1, $id, PDO::PARAM_INT);
        $stmt_update->execute();

        // Ambil buku_id dari tabel peminjaman
        $select_query = "SELECT buku_id FROM peminjaman WHERE id = ?";
        $stmt_select = $conn->prepare($select_query);
        $stmt_select->bindValue(1, $id, PDO::PARAM_INT);
        $stmt_select->execute();
        $row = $stmt_select->fetch(PDO::FETCH_ASSOC);

        $buku_id = $row['buku_id'];

        // Update status buku ke 'tersedia'
        $update_buku_query = "UPDATE buku SET status = 'tersedia' WHERE id = ?";
        $stmt_update_buku = $conn->prepare($update_buku_query);
        $stmt_update_buku->bindValue(1, $buku_id, PDO::PARAM_INT);
        $stmt_update_buku->execute();

        echo "Buku berhasil dikembalikan!";
    }

    header("Location: peminjaman_list.php");
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}