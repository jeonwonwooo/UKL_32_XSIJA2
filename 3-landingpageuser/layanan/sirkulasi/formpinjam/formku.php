<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();

include 'formkoneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Harus login dulu!");
}

$buku_id = filter_input(INPUT_GET, 'buku_id', FILTER_VALIDATE_INT);
if (!$buku_id) {
    die("ID BUKU TIDAK VALID");
}

$stmt = $conn->prepare("SELECT judul, tipe_buku FROM buku WHERE id = ?");
$stmt->execute([$buku_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    die("BUKU TIDAK ADA");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->beginTransaction();
    try {
        // Insert peminjaman
        $tanggal_pinjam = date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO peminjaman (anggota_id, buku_id, tanggal_pinjam, status, tipe_buku)
                              VALUES (?, ?, ?, 'dipinjam', ?)");
        $stmt->execute([$_SESSION['user_id'], $buku_id, $tanggal_pinjam, $book['tipe_buku']]);
        
        $last_id = $conn->lastInsertId();

        // Update status buku jika buku fisik
        if ($book['tipe_buku'] === 'Buku Fisik') {
            $conn->exec("UPDATE buku SET status = 'dipinjam' WHERE id = $buku_id");
        }

        $conn->commit();
        header("Location: peminjaman_struk.php?id=$last_id");
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        die("ERROR SYSTEM: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Peminjaman</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Form Peminjaman</h1>
    <form action="" method="POST">
        <div class="mb-3">
            <label for="judul_buku">Judul Buku</label>
            <input type="text" id="judul_buku" value="<?= htmlspecialchars($book['judul']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="tipe_buku">Tipe Buku</label>
            <input type="text" id="tipe_buku" value="<?= htmlspecialchars(ucfirst($book['tipe_buku'])) ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="tanggal_pinjam">Tanggal Pinjam</label>
            <input type="text" id="tanggal_pinjam" value="<?= date('Y-m-d') ?>" readonly>
        </div>
        <button type="submit" class="btn btn-primary">Konfirmasi Peminjaman</button>
        <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/katalog/katalog.php" class="btn btn-secondary">Kembali ke Katalog</a>
    </form>
</div>
</body>
</html>