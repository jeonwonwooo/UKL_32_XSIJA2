<?php
session_start();
include 'formkoneksi.php';

if (!isset($_SESSION['user_id'])) {
    die('<h2 style="color:red">Akses ditolak. Silakan login terlebih dahulu.</h2>');
}

$peminjaman_id = $_GET['peminjaman_id'] ?? null;
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

$stmt = $conn->prepare("
    SELECT buku.tipe_buku 
    FROM peminjaman 
    JOIN buku ON peminjaman.buku_id = buku.id 
    WHERE peminjaman.id = ?
");
$stmt->execute([$peminjaman_id]);
$result = $stmt->fetch();

if (!$result) {
    die("PEMINJAMAN TIDAK DITEMUKAN");
}

$tipe_buku = $result['tipe_buku'];

if ($tipe_buku === 'Buku Fisik') {
    try {
        $conn->beginTransaction();

        // Update status peminjaman
        $update_query = "UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = CURDATE() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->execute([$peminjaman_id]);

        // Update status buku
        $conn->exec("UPDATE buku SET status = 'tersedia' WHERE id = (SELECT buku_id FROM peminjaman WHERE id = $peminjaman_id)");

        $conn->commit();
        echo "Peminjaman berhasil dicatat sebagai dikembalikan.";
    } catch (Exception $e) {
        $conn->rollBack();
        die("ERROR SYSTEM: " . $e->getMessage());
    }
} else if ($tipe_buku === 'Buku Elektronik') {
    echo "Buku eBook tidak memerlukan pengembalian.";
} else {
    echo "Tipe buku tidak dikenali.";
}