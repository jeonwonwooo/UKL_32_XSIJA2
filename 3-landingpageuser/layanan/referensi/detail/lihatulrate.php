<?php
session_start();
require_once 'formkoneksi.php';

// Ambil ID dokumen dari URL
$dokumen_id = $_GET['id'] ?? null;

// Jika tidak ada dokumen_id, redirect ke halaman lain
if (!$dokumen_id) {
    header("Location: detail_dokumen.php");
    exit;
}

// Fungsi untuk mendapatkan foto profil
function getFotoProfile($foto_profil) {
    $default_photo = '/CODINGAN/assets/default_profile.jpg';
    
    if (empty($foto_profil)) {
        return $default_photo;
    }
    
    // Cek apakah file foto profil ada
    $photo_path = $_SERVER['DOCUMENT_ROOT'] . $foto_profil;
    if (!file_exists($photo_path)) {
        return $default_photo;
    }
    
    return $foto_profil;
}

// Ambil daftar ulasan dengan foto profil
$ulasan_query = "SELECT r.id, r.anggota_id, r.dokumen_id, r.nilai, r.ulasan, r.created_at, 
                        u.username, u.nama, u.foto_profil 
                 FROM rating r 
                 JOIN anggota u ON r.anggota_id = u.id 
                 WHERE r.dokumen_id = ?
                 ORDER BY r.created_at DESC";
$ulasan_stmt = $conn->prepare($ulasan_query);
$ulasan_stmt->execute([$dokumen_id]);
$ulasan_list = $ulasan_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil nama dokumen dan info tambahan untuk ditampilkan di halaman
$dokumen_query = "SELECT judul, penulis FROM dokumen WHERE id = ?";
$dokumen_stmt = $conn->prepare($dokumen_query);
$dokumen_stmt->execute([$dokumen_id]);
$dokumen_data = $dokumen_stmt->fetch();

if (!$dokumen_data) {
    header("Location: detail_dokumen.php");
    exit;
}

$dokumen_judul = $dokumen_data['judul'];
$dokumen_penulis = $dokumen_data['penulis'];

// Hitung rata-rata rating
$rata_rata = 0;
$total_ulasan = count($ulasan_list);
if ($total_ulasan > 0) {
    $total_nilai = array_sum(array_column($ulasan_list, 'nilai'));
    $rata_rata = round($total_nilai / $total_ulasan, 1);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Ulasan - <?= htmlspecialchars($dokumen_judul) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/> 
    <link rel="stylesheet" href="lihat_semua_ulasan.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dokumen-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 12px;
            text-align: center;
        }

        .dokumen-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .dokumen-author {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .rating-summary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        .avg-rating {
            font-size: 24px;
            font-weight: bold;
        }

        .rating-stars {
            display: flex;
            gap: 2px;
        }

        .rating-stars i {
            color: #ffd700;
            font-size: 18px;
        }

        .total-reviews {
            font-size: 14px;
            opacity: 0.8;
        }

        .reviews-container {
            margin-bottom: 30px;
        }

        .review {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .review:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            gap: 12px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e0e0e0;
            background-color: #f5f5f5;
        }

        .user-details {
            flex: 1;
        }

        .username {
            font-weight: bold;
            color: #333;
            display: block;
            font-size: 16px;
            margin-bottom: 2px;
        }

        .date {
            color: #666;
            font-size: 13px;
        }

        .review-rating {
            margin-bottom: 12px;
        }

        .review-rating i {
            color: #ffd700;
            font-size: 16px;
            margin-right: 2px;
        }

        .review-rating i.empty {
            color: #ddd;
        }

        .review-comment {
            color: #444;
            line-height: 1.6;
            margin: 0;
            font-size: 15px;
        }

        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-style: italic;
        }

        .no-reviews i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #667eea;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .back-button:hover {
            background: #5a6fd8;
            text-decoration: none;
            color: white;
        }

        .back-button i {
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header Dokumen -->
    <div class="dokumen-header">
        <div class="dokumen-title"><?= htmlspecialchars($dokumen_judul) ?></div>
        <div class="dokumen-author">oleh <?= htmlspecialchars($dokumen_penulis) ?></div>
        
        <?php if ($total_ulasan > 0): ?>
        <div class="rating-summary">
            <span class="avg-rating"><?= $rata_rata ?></span>
            <div class="rating-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($i <= floor($rata_rata)): ?>
                        <i class="fas fa-star"></i>
                    <?php elseif ($i <= ceil($rata_rata) && $rata_rata - floor($rata_rata) >= 0.5): ?>
                        <i class="fas fa-star-half-alt"></i>
                    <?php else: ?>
                        <i class="far fa-star"></i>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <span class="total-reviews">(<?= $total_ulasan ?> ulasan)</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Container Ulasan -->
    <div class="reviews-container">
        <?php if (!empty($ulasan_list)): ?>
            <?php foreach ($ulasan_list as $review): ?>
                <div class="review">
                    <div class="user-info">
                        <img src="<?= htmlspecialchars(getFotoProfile($review['foto_profil'])) ?>" 
                             alt="Foto Profil <?= htmlspecialchars($review['nama'] ?? $review['username']) ?>" 
                             class="avatar"
                             onerror="this.src='/CODINGAN/assets/default_profile.jpg'">
                        <div class="user-details">
                            <span class="username">
                                <?= htmlspecialchars($review['nama'] ?? $review['username']) ?>
                            </span>
                            <?php if (!empty($review['created_at'])): ?>
                                <span class="date">
                                    <i class="far fa-clock"></i>
                                    <?= date('d M Y, H:i', strtotime($review['created_at'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="review-rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="<?= $i <= $review['nilai'] ? 'fas fa-star' : 'far fa-star empty' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    
                    <p class="review-comment"><?= nl2br(htmlspecialchars($review['ulasan'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-reviews">
                <i class="far fa-comment-alt"></i>
                <p>Belum ada ulasan untuk dokumen ini.</p>
                <p>Jadilah yang pertama memberikan ulasan!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tombol Kembali -->
    <a href="detail_dokumen.php?id=<?= $dokumen_id ?>" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Kembali ke Detail Dokumen
    </a>
</div>

</body>
</html>