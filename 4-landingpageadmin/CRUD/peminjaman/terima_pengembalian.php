<?php
include 'formkoneksi.php';

$peminjaman_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

try {
    // Update status peminjaman dan buku
    $conn->beginTransaction();

    // Update status peminjaman
    $updatePeminjaman = $conn->prepare("UPDATE peminjaman 
                                        SET status = 'dikembalikan', tanggal_kembali = NOW(), status_pengajuan = 'diterima' 
                                        WHERE id = ?");
    $updatePeminjaman->execute([$peminjaman_id]);

    // Update status buku menjadi tersedia
    $updateBuku = $conn->prepare("UPDATE buku 
                                  SET status = 'tersedia' 
                                  WHERE id = (SELECT buku_id FROM peminjaman WHERE id = ?)");
    $updateBuku->execute([$peminjaman_id]);

    $conn->commit();
    echo "Pengembalian berhasil diterima.";
} catch (Exception $e) {
    $conn->rollBack();
    die("ERROR: " . $e->getMessage());
}
?>