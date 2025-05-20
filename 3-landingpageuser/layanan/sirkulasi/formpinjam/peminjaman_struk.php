<?php
session_start();

// [2] DATABASE CONNECTION
include 'formkoneksi.php';
if (!$conn) {
    die("DATABASE DOWN: Cek koneksi database Anda");
}

// [3] SECURITY VALIDATION
if (!isset($_SESSION['user_id'])) {
    die("HARUS LOGIN DULU!");
}

// [4] GET ID PEMINJAMAN
$peminjaman_id =
    $_GET['id'] ??
    $_SESSION['last_pinjam_id'] ??
    $conn->query("SELECT MAX(id) FROM peminjaman WHERE anggota_id = " . $_SESSION['user_id'])->fetchColumn() ??
    die("ID PEMINJAMAN TIDAK VALID");

// [5] DATA VALIDATION
try {
    $sql = "SELECT p.*, b.judul, b.tipe_buku, a.username 
            FROM peminjaman p
            JOIN buku b ON p.buku_id = b.id
            JOIN anggota a ON p.anggota_id = a.id
            WHERE p.id = ? AND p.anggota_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt->execute([$peminjaman_id, $_SESSION['user_id']])) {
        throw new Exception("Gagal eksekusi query");
    }
    $peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$peminjaman) {
        throw new Exception("Data peminjaman tidak ditemukan atau tidak berhak mengakses.");
    }
} catch (Exception $e) {
    die("ERROR: " . $e->getMessage());
}
unset($_SESSION['last_pinjam_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Peminjaman</title>
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link rel="stylesheet" href="peminjaman_struk.css">
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div class="header">
            <h1>Perpustakaan SMA Rivenhill</h1>
            <p>Harap <em>screenshot</em> struk ini untuk keperluan administrasi.</p>
        </div>
        <div class="content">
            <h2>Struk Peminjaman</h2>
            <table>
                <tr><th>ID Peminjaman</th><td><?= htmlspecialchars($peminjaman['id']) ?></td></tr>
            <tr><th>Username</th><td><?= htmlspecialchars($peminjaman['username']) ?></td></tr>
            <tr><th>Judul Buku</th><td><?= htmlspecialchars($peminjaman['judul']) ?></td></tr>
            <tr><th>Tipe Buku</th><td><?= htmlspecialchars(ucfirst($peminjaman['tipe_buku'])) ?></td></tr>
            <tr><th>Tanggal Pinjam</th><td><?= htmlspecialchars($peminjaman['tanggal_pinjam']) ?></td></tr>
            <tr><th>Batas Pengembalian</th><td><?= htmlspecialchars($peminjaman['batas_pengembalian']) ?></td></tr>
            </table>
        </div>
        <div style="margin-top: 20px; text-align: right;">
            <a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/aktivitas.php" class="aktivitas-btn">Lihat Aktivitas</a>
        </div>
        <div class="footer">
            <p>Terima kasih telah menggunakan layanan kami.</p>
            <p>Buku harus dikembalikan sebelum tanggal <?= htmlspecialchars($peminjaman['batas_pengembalian'] ?? 'N/A') ?>.</p>
        </div>
    </div>
</body>
</html>