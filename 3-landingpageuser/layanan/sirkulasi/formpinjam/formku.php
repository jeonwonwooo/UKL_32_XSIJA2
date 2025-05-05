<?php
// [1] INITIAL SETUP
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();

// [2] DATABASE CONNECTION
include 'formkoneksi.php';
if (!$conn) {
    die("DATABASE DOWN: Cek file formkoneksi.php");
}

// [3] SECURITY VALIDATION
if (!isset($_SESSION['user_id'])) {
    die("HARUS LOGIN DULU!");
}

$buku_id = filter_input(INPUT_GET, 'buku_id', FILTER_VALIDATE_INT);
if (!$buku_id) {
    die("ID BUKU TIDAK VALID");
}

// [4] BOOK AVAILABILITY CHECK - UPDATED TO INCLUDE tipe_buku
$stmt = $conn->prepare("SELECT judul, tipe_buku FROM buku WHERE id = ? AND status = 'tersedia'");
if (!$stmt->execute([$buku_id])) {
    die("ERROR: Gagal memeriksa ketersediaan buku");
}

$book = $stmt->fetch();

if (!$book) {
    die("BUKU SUDAH DIPINJAM/TIDAK ADA");
}

// [5] PINJAM PROCESS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->beginTransaction();
    try {
        // [A] INSERT PEMINJAMAN
        $sql = "INSERT INTO peminjaman (anggota_id, buku_id, tanggal_pinjam, status, tipe_buku) 
                VALUES (?, ?, ?, 'dipinjam', ?)";
        $tanggal_pinjam = date('Y-m-d');

        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $buku_id, $tanggal_pinjam, $book['tipe_buku']]);

        // [B] GET LAST ID (3 LAYER FALLBACK)
        $last_id = $conn->lastInsertId(); // METHOD 1
        if (!$last_id) {
            $last_id = $conn->query("SELECT MAX(id) FROM peminjaman WHERE anggota_id = " . $_SESSION['user_id'])->fetchColumn(); // METHOD 2
        }
        if (!$last_id) {
            throw new Exception("GAGAL DAPATKAN ID");
        }

        // [C] UPDATE BOOK STATUS
        if ($book['tipe_buku'] === 'fisik') {
            $conn->exec("UPDATE buku SET status = 'dipinjam' WHERE id = $buku_id");
        } elseif ($book['tipe_buku'] === 'ebook') {
            // Untuk eBook, tidak perlu mengubah status buku
        }

        $conn->commit();

        // [D] REDIRECT (3 OPTIONS)
        $_SESSION['last_pinjam_id'] = $last_id; // BACKUP 1
        header("Location: peminjaman_struk.php?id=" . $last_id);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
</head>

<body>
    <div class="container">
        <h1>Form Peminjaman</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="judul_buku" class="form-label">Judul Buku</label>
                <input type="text" class="form-control" id="judul_buku" value="<?= htmlspecialchars($book['judul'] ?? '') ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="tipe_buku" class="form-label">Tipe Buku</label>
                <input type="text" class="form-control" id="tipe_buku" value="<?= isset($book['tipe_buku']) ? htmlspecialchars(ucfirst($book['tipe_buku'])) : '' ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam</label>
                <input type="text" class="form-control" id="tanggal_pinjam" value="<?= date('Y-m-d') ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Konfirmasi Peminjaman</button>
            <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/katalog/katalog.php" class="btn btn-secondary">Kembali ke Katalog</a>
        </form>
    </div>
</body>

</html>