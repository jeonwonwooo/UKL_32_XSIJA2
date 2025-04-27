<?php
session_start();

// [1] CEK LOGIN
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.0 403 Forbidden');
    die('<h2 style="color:red">Akses ditolak. Silakan login terlebih dahulu.</h2>');
}

// [2] SANITASI INPUT
$peminjaman_id = $_GET['peminjaman_id'] ?? null;

if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

// [3] AMBIL DATA PEMINJAMAN
$stmt = $conn->prepare("SELECT buku.tipe_buku FROM peminjaman JOIN buku ON peminjaman.buku_id = buku.id WHERE peminjaman.id = ?");
$stmt->execute([$peminjaman_id]);
$result = $stmt->fetch();

if (!$result) {
    die("PEMINJAMAN TIDAK DITEMUKAN");
}

$tipe_buku = $result['tipe_buku'];

// [4] PROSES PENGEMBALIAN
if ($tipe_buku === 'fisik') {
    $conn->beginTransaction();
    try {
        // Catat tanggal pengembalian
        $update_query = "UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = CURDATE() WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->execute([$peminjaman_id]);

        // Update status buku kembali tersedia
        $conn->exec("UPDATE buku SET status = 'tersedia' WHERE id = (SELECT buku_id FROM peminjaman WHERE id = $peminjaman_id)");

        $conn->commit();
        
        echo "Peminjaman berhasil dicatat sebagai dikembalikan.";
    } catch (Exception $e) {
        $conn->rollBack();
        die("ERROR SYSTEM: " . $e->getMessage());
    }
} else {
    echo "Buku eBook tidak memerlukan pengembalian.";
}

// [5] HEADER PDF
$file = $_GET['file'] ?? '';
$file = basename($file); // Hapus path traversal
$file = preg_replace('/[^\w\s\-\.()]/u', '', $file); // Hapus karakter khusus
$file = trim($file); // Hilangkan spasi di awal/akhir

$base_dir = __DIR__ . "/uploads/";
$file_path = $base_dir . $file;

if (!file_exists($file_path)) {
    die('<h2 style="color:red">File tidak ditemukan. Pastikan nama file benar!</h2>');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . htmlspecialchars($file) . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>