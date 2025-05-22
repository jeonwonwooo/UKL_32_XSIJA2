<?php
include '../formkoneksi.php';

// Cek apakah user sudah login
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Harus login dulu!");
}

// Ambil ID dokumen dari URL
$dokumen_id = $_GET['id'] ?? null;

// Ambil data dokumen
$stmt = $conn->prepare("SELECT * FROM dokumen WHERE id = ?");
$stmt->execute([$dokumen_id]);
$dokumen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dokumen) {
    die("Dokumen tidak ditemukan!");
}

// Ambil rata-rata rating dan jumlah ulasan
$stmt = $conn->prepare("SELECT COALESCE(AVG(nilai), 0) as rata_rating, COUNT(*) as jumlah_ulasan FROM rating WHERE dokumen_id = ?");
$stmt->execute([$dokumen_id]);
$rating_result = $stmt->fetch(PDO::FETCH_ASSOC);
$rata_rating = round($rating_result['rata_rating'], 1);
$jumlah_ulasan = $rating_result['jumlah_ulasan'];

// Cek apakah dokumen sudah ditambahkan ke favorit
$stmt = $conn->prepare("SELECT * FROM favorit_dokumen WHERE dokumen_id = ? AND user_id = ?");
$stmt->execute([$dokumen_id, $_SESSION['user_id']]);
$favorit_aktif = $stmt->rowCount() > 0;

function getIconClass($tipe_dokumen) {
    switch ($tipe_dokumen) {
        case 'artikel_konferensi':
            return 'fa-file-alt';
        case 'jurnal':
            return 'fa-book';
        case 'modul_pelajaran':
            return 'fa-chalkboard-teacher';
        case 'laporan':
            return 'fa-scroll';
        case 'skripsi':
            return 'fa-graduation-cap';
        default:
            return 'fa-file';
    }
}
$status = $_GET['status'] ?? '';
$notif = '';
if ($status === 'success') {
    $notif = '<p style="color: green; text-align: center;">Dokumen berhasil ditambahkan ke favorit!</p>';
} elseif ($status === 'exists') {
    $notif = '<p style="color: red; text-align: center;">Dokumen sudah ada di daftar favorit!</p>';
} elseif ($status === 'removed') {
    $notif = '<p style="color: blue; text-align: center;">Dokumen berhasil dihapus dari favorit!</p>';
}
?>
<?= $notif ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Dokumen - <?= htmlspecialchars($dokumen['judul']) ?></title>
    <link rel="stylesheet" href="detail_dokumen.css">
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css ">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../../logo.png" alt="Logo Perpus" />
        </div>
        <nav class="navbar">
            <ul>
                <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.html">Beranda</a></li>
                <li><a href="#">Katalog</a></li>
                <li><a href="#">Aktivitas</a></li>
                <li><a href="#">Favorit</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.html">Kontak</a></li>
                <li class="profil"><a href="#"><i class="fas fa-user"></i></a></li>
                <li>
                    <button class="btn-logout">
                        <i class="fas fa-arrow-left"></i>
                        <a href="/CODINGAN/3-landingpageuser/beranda/beranda.html">Kembali</a>
                    </button>
                </li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="judul">
            <h1>Detail Dokumen</h1>
            <h2><?= htmlspecialchars($dokumen['judul']) ?></h2>
        </section>
        <section class="detail-dokumen">
            <div class="document-icon">
    <i class="fas <?= getIconClass($dokumen['tipe_dokumen']) ?>"></i>
</div>
            <div class="informasi-dokumen">
                <p><strong>Penulis:</strong> <?= htmlspecialchars($dokumen['penulis']) ?></p>
                <p><strong>Tahun Terbit:</strong> <?= htmlspecialchars($dokumen['tahun_terbit']) ?></p>
                <p><strong>Jenis:</strong> <?= htmlspecialchars($dokumen['tipe_dokumen']) ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($dokumen['status']) ?></p>
                <p><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($dokumen['deskripsi'])) ?></p>
            </div>
        </section>
        <section class="rating">
            <!-- Rating Overview -->
            <div class="rating-overview">
                <p><strong>Rating:</strong>
                    ★ (<?= number_format($rata_rating, 1) ?>/5) <?= $jumlah_ulasan ?> ratings - <?= $jumlah_ulasan ?> reviews
                </p>
            </div>
            <!-- Star Distribution -->
            <div class="star-distribution">
                <?php if (!empty($distribusi_rating)): ?>
                    <?php foreach ($distribusi_rating as $row):
                        $percentage = ($jumlah_ulasan > 0) ? ($row['count'] / $jumlah_ulasan) * 100 : 0;
                    ?>
                        <div class="rating-bar">
                            <span><?= $row['rating'] ?> stars</span>
                            <div class="bar-container">
                                <div class="bar" style="width: <?= $percentage ?>%;"></div>
                            </div>
                            <span><?= $row['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Tidak ada rating untuk dokumen ini.</p>
                <?php endif; ?>
            </div>
            <!-- User Reviews -->
            <section class="user-reviews">
                <h3>Ulasan</h3>
                <p>Displaying 1 of <?= $jumlah_ulasan ?> reviews</p>
                <?php if (!empty($ulasan)): ?>
                    <?php foreach ($ulasan as $review): ?>
                        <div class="review">
                            <div class="review-header">
                                <span><?= htmlspecialchars($review['username']) ?></span>
                                <span>
                                    <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                        ★
                                    <?php endfor; ?>
                                </span>
                            </div>
                            <p class="review-comment"><?= htmlspecialchars($review['komentar']) ?></p>
                            <small class="show-more">Show more ▼</small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reviews-container">
                        <p>Belum ada ulasan untuk dokumen ini.</p>
                        <a href="888.php?id=<?= $dokumen_id ?>" class="btn-tambah-ulasan">Tambah Ulasan</a>
                    </div>
                <?php endif; ?>
            </section>
        </section>
        <div class="action-buttons">
    <?php if ($dokumen['status'] === 'tersedia' && !empty($dokumen['file_path'])): ?>
        <!-- Tombol untuk melihat dokumen -->
        <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/formpinjam/view_pdf.php?file=<?= urlencode(basename($dokumen['file_path'])) ?>"
           target="_blank"
           class="btn-pinjam">Lihat Sekarang</a>
    <?php else: ?>
        <span class="btn-pinjam disabled">Dokumen Tidak Tersedia</span>
    <?php endif; ?>
            <!-- Tombol Tambah ke Favorit -->
            <form action="prosesfav.php" method="POST" style="display: inline-block;">
                <input type="hidden" name="dokumen_id" value="<?= $dokumen_id ?>">
                <?php if ($favorit_aktif): ?>
                    <!-- Tombol Hapus dari Favorit -->
                    <input type="hidden" name="action" value="hapus">
                    <button type="submit" class="btn-favorit btn-hapus">
                        Hapus dari Favorit
                    </button>
                <?php else: ?>
                    <!-- Tombol Tambah ke Favorit -->
                    <input type="hidden" name="action" value="tambah">
                    <button type="submit" class="btn-favorit btn-tambah">
                        Tambah ke Favorit
                    </button>
                <?php endif; ?>
            </form>
        </div>
        <p id="notif-favorit" style="display: none; color: green;">Dokumen berhasil ditambahkan ke favorit Anda!</p>
    </main>
    <footer class="footer">
        <div class="container">
            <div class="left">
                <img src="logo.png" alt="Library of Riverhill Senior High School logo" />
                <p>
                    Lorem ipsum dolor sit amet consectetur adipiscing elit. Repudiandae
                    omnis molestias nobis. Lorem ipsum dolor sit amet consectetur
                    adipiscing elit. Repudiandae omnis molestias nobis.
                </p>
                <div class="social-icons">
                    <a href="https://wa.me/6285936164597 " target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.linkedin.com/in/syarivatun-nisa-i-nur-aulia-3ab52b2bb/ " target="_blank"><i
                            class="fab fa-linkedin"></i></a>
                    <a href="https://instagram.com/jeonwpnwoo " target="_blank"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="right">
                <h2>Tautan Fungsional</h2>
                <ul>
                    <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.html">Beranda</a></li>
                    <li>
                        <a href="/CODINGAN/3-landingpageuser/layanan/layanan.html">Layanan</a>
                    </li>
                    <li>
                        <a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            Copyright © 2024 Library of Riverhill Senior High School. All Rights
            Reserved
        </div>
    </footer>
</body>
</html>