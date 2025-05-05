<?php
// [1] INITIAL SETUP
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
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

// [4] GET ID (MULTI-SOURCE FALLBACK)
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

    $peminjaman = $stmt->fetch();

    if (!$peminjaman) {
        throw new Exception("Data peminjaman tidak ditemukan");
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .content table {
            width: 100%;
            border-collapse: collapse;
        }

        .content table th,
        .content table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="header">
            <h1>Perpustakaan SMA Rivenhill</h1>
            <p>Jl. Contoh No. 123, Kota Bandung</p>
        </div>

        <div class="content">
            <h2>Struk Peminjaman</h2>
            <table>
                <tr>
                    <th>ID Peminjaman</th>
                    <td><?= htmlspecialchars($peminjaman['id'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Username</th>
                    <td><?= htmlspecialchars($peminjaman['username'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Judul Buku</th>
                    <td><?= htmlspecialchars($peminjaman['judul'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Tipe Buku</th>
                    <td><?= isset($peminjaman['tipe_buku']) ? htmlspecialchars(ucfirst($peminjaman['tipe_buku'])) : 'N/A' ?></td>
                </tr>
                <tr>
                    <th>Tanggal Pinjam</th>
                    <td><?= htmlspecialchars($peminjaman['tanggal_pinjam'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Batas Pengembalian</th>
                    <td><?= htmlspecialchars($peminjaman['batas_pengembalian'] ?? 'N/A') ?></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Terima kasih telah menggunakan layanan kami.</p>
            <p>Buku harus dikembalikan sebelum tanggal <?= htmlspecialchars($peminjaman['batas_pengembalian'] ?? 'N/A') ?>.</p>
        </div>
    </div>
</body>

</html>