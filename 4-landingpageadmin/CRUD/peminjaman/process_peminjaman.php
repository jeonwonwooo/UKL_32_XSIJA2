<?php
include 'formkoneksi.php';

// Ambil ID peminjaman dari GET
$peminjaman_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

try {
    // Mulai transaksi
    $conn->beginTransaction();

    // Update status peminjaman menjadi 'dikembalikan'
    $update_query = "
        UPDATE peminjaman
        SET status = 'dikembalikan', status_pengajuan = 'diterima', tanggal_kembali = CURDATE()
        WHERE id = ?
    ";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bindValue(1, $peminjaman_id, PDO::PARAM_INT);
    $stmt_update->execute();

    // Ambil buku_id dari tabel peminjaman
    $select_query = "SELECT buku_id FROM peminjaman WHERE id = ?";
    $stmt_select = $conn->prepare($select_query);
    $stmt_select->bindValue(1, $peminjaman_id, PDO::PARAM_INT);
    $stmt_select->execute();
    $row = $stmt_select->fetch(PDO::FETCH_ASSOC);

    $buku_id = $row['buku_id'];

    // Update status buku ke 'tersedia'
    $update_buku_query = "UPDATE buku SET status = 'tersedia' WHERE id = ?";
    $stmt_update_buku = $conn->prepare($update_buku_query);
    $stmt_update_buku->bindValue(1, $buku_id, PDO::PARAM_INT);
    $stmt_update_buku->execute();

    // Commit transaksi
    $conn->commit();

    // Redirect dengan parameter notifikasi
    header("Location: peminjaman_list.php?status=pengajuan_diterima");
    exit;
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollBack();
    die("ERROR: " . $e->getMessage());
}
?>