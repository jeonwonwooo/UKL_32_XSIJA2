<?php
session_start();
require_once 'formkoneksi.php';

// Ambil ID buku dari URL
$buku_id = isset($_GET['id']) ? $_GET['id'] : null;

// Jika tidak ada buku_id, redirect ke halaman lain
if (!$buku_id) {
    header("Location: daftar_buku.php");
    exit;
}

// Ambil daftar ulasan
$ulasan_query = "SELECT r.id, r.anggota_id, r.buku_id, r.nilai, r.ulasan, u.username 
                 FROM rating r 
                 JOIN anggota u ON r.anggota_id = u.id 
                 WHERE r.buku_id = ?";
$ulasan_stmt = $conn->prepare($ulasan_query);
$ulasan_stmt->execute([$buku_id]);
$ulasan_list = $ulasan_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil nama buku untuk ditampilkan di halaman
$buku_query = "SELECT judul FROM buku WHERE id = ?";
$buku_stmt = $conn->prepare($buku_query);
$buku_stmt->execute([$buku_id]);
$buku_data = $buku_stmt->fetch(PDO::FETCH_ASSOC);
$buku_judul = $buku_data['judul'] ?? 'Buku Tidak Ditemukan';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Ulasan - <?= htmlspecialchars($buku_judul) ?></title>
    <style>
        /* CSS Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .review {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #f8f8f8;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .review-header span:first-child {
            font-weight: 600;
            font-size: 15px;
        }
        .review-header span:last-child {
            color: #f5c518; /* Warna bintang emas */
            font-size: 16px;
        }
        .review-comment {
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Semua Ulasan untuk Buku: <?= htmlspecialchars($buku_judul) ?></h1>

        <?php if (!empty($ulasan_list)): ?>
            <?php foreach ($ulasan_list as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <span><?= htmlspecialchars($review['username']) ?></span>
                        <span>
                            <?php for ($i = 0; $i < $review['nilai']; $i++): ?>
                                ★
                            <?php endfor; ?>
                        </span>
                    </div>
                    <p class="review-comment"><?= htmlspecialchars($review['ulasan']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Belum ada ulasan untuk buku ini.</p>
        <?php endif; ?>

        <!-- Tombol Kembali -->
        <a href="detail_buku.php?id=<?= $buku_id ?>" class="back-button">Kembali ke Detail Buku</a>
    </div>
</body>
</html>