<?php
session_start();
include 'formkoneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("HARUS LOGIN DULU!");
}

$peminjaman_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

// Ambil data peminjaman
$query = "SELECT * FROM peminjaman WHERE id = ? AND anggota_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$peminjaman_id, $_SESSION['user_id']]);
$peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$peminjaman) {
    die("DATA PEMINJAMAN TIDAK DITEMUKAN");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update kolom pengajuan_pengembalian
        $update = $conn->prepare("UPDATE peminjaman 
                                  SET pengajuan_pengembalian = NOW(), status_pengajuan = 'menunggu' 
                                  WHERE id = ?");
        $update->execute([$peminjaman_id]);

        echo "Pengajuan pengembalian berhasil diajukan. Admin akan memverifikasi dalam 24 jam.";
    } catch (Exception $e) {
        die("ERROR: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Pengembalian</title>
    <link rel="stylesheet" href="form_pengembalian.css">
</head>
<body>
<div class="container">
    <h1>Form Pengembalian</h1>
    <p>Anda akan mengajukan pengembalian buku berikut:</p>
    <p><strong>Judul Buku:</strong> <?= htmlspecialchars($peminjaman['judul_buku']) ?></p>
    <form action="" method="POST">
        <button type="submit" class="btn btn-primary">Ajukan Pengembalian</button>
        <a href="aktivitas.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>