<?php
include 'formkoneksi.php';

// Ambil id peminjaman dari URL
$id = $_GET['id'] ?? '';

// Validasi input
if (empty($id)) {
    die("ID peminjaman tidak valid.");
}

try {
    // 1. Ambil buku_id dari tabel peminjaman
    $select_query = "SELECT buku_id FROM peminjaman WHERE id = ?";
    $stmt_select = $conn->prepare($select_query);
    $stmt_select->bindValue(1, $id, PDO::PARAM_INT);
    $stmt_select->execute();
    
    $row = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Data peminjaman tidak ditemukan.");
    }

    $buku_id = $row['buku_id'];

    // 2. Hapus data denda terkait peminjaman
    $delete_denda_query = "DELETE FROM denda WHERE peminjaman_id = ?";
    $stmt_delete_denda = $conn->prepare($delete_denda_query);
    $stmt_delete_denda->bindValue(1, $id, PDO::PARAM_INT);
    $stmt_delete_denda->execute();

    // 3. Hapus data peminjaman
    $delete_peminjaman_query = "DELETE FROM peminjaman WHERE id = ?";
    $stmt_delete_peminjaman = $conn->prepare($delete_peminjaman_query);
    $stmt_delete_peminjaman->bindValue(1, $id, PDO::PARAM_INT);
    $stmt_delete_peminjaman->execute();

    // 4. Update status buku menjadi tersedia
    $update_buku_query = "UPDATE buku SET status = 'tersedia' WHERE id = ?";
    $stmt_update_buku = $conn->prepare($update_buku_query);
    $stmt_update_buku->bindValue(1, $buku_id, PDO::PARAM_INT);
    $stmt_update_buku->execute();

    echo "Data peminjaman berhasil dihapus, data denda terkait dihapus, dan status buku diperbarui ke 'tersedia'.";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Redirect setelah proses selesai
header("Location: peminjaman_list.php");
exit;
?>