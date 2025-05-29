<?php
include 'formkoneksi.php';

$peminjaman_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

try {
    // Update status pengajuan menjadi ditolak
    $update = $conn->prepare("UPDATE peminjaman 
                              SET status_pengajuan = 'ditolak', pengajuan_pengembalian = NULL 
                              WHERE id = ?");
    $update->execute([$peminjaman_id]);

    echo "Pengembalian berhasil ditolak.";
} catch (Exception $e) {
    die("ERROR: " . $e->getMessage());
}
?>