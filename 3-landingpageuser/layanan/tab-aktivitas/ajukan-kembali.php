<?php
session_start();
include 'formkoneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    die("HARUS LOGIN DULU!");
}

$peminjaman_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

try {
    // Ambil data peminjaman
    $query = "SELECT p.*, b.judul AS judul_buku 
              FROM peminjaman p
              JOIN buku b ON p.buku_id = b.id
              WHERE p.id = ? AND p.anggota_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$peminjaman_id, $_SESSION['user_id']]);
    $peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$peminjaman) {
        die("Data peminjaman tidak ditemukan atau Anda tidak berhak mengakses.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ajukan Pengembalian</title>
    <link rel="stylesheet" href="ajukan-kembali.css">
</head>
<body>
<div class="container">
    <h1>Ajukan Pengembalian Buku</h1>
    <p>Anda sedang mengajukan pengembalian untuk buku berikut:</p>
    <table>
        <tr>
            <th>Judul Buku</th>
            <td><?= htmlspecialchars($peminjaman['judul_buku']) ?></td>
        </tr>
        <tr>
            <th>Tanggal Pinjam</th>
            <td><?= htmlspecialchars($peminjaman['tanggal_pinjam']) ?></td>
        </tr>
        <tr>
            <th>Batas Pengembalian</th>
            <td><?= htmlspecialchars($peminjaman['batas_pengembalian']) ?></td>
        </tr>
    </table>
    <div class="actions">
        <a href="proses_pengajuan.php?id=<?= $peminjaman['id'] ?>" class="btn btn-primary">Ajukan Pengembalian</a>
        <a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/aktivitas.php" class="btn btn-secondary">Kembali ke Aktivitas</a>
    </div>
</div>
</body>
</html>