<?php
session_start();
include 'formkoneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Harus login dulu!");
}

$peminjaman_id = $_GET['id'] ?? $_SESSION['last_pinjam_id'] ?? null;
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK DITEMUKAN");
}

try {
    $sql = "SELECT p.id, b.judul, b.tipe_buku, p.tanggal_pinjam, DATE_ADD(p.tanggal_pinjam, INTERVAL 7 DAY) AS batas_pengembalian
            FROM peminjaman p
            JOIN buku b ON p.buku_id = b.id
            WHERE p.id = ? AND p.anggota_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$peminjaman_id, $_SESSION['user_id']]);
    $peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$peminjaman) {
        die("Data peminjaman tidak ditemukan atau tidak berhak mengakses.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
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