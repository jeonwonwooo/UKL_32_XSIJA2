<?php
include 'formkoneksi.php';

if (!isset($_GET['id'])) {
    echo "ID buku tidak ditemukan!";
    exit;
}

$buku_id = $_GET['id'];

$query = "SELECT * FROM buku WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buku) {
    echo "Buku tidak ditemukan!";
    exit;
}

$query = "SELECT pb.*, pk.nama_kategori FROM buku pb
          LEFT JOIN kategori pk ON pb.kategori_id = pk.id
          WHERE pb.id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

$query = "SELECT COALESCE(AVG(nilai), 0) as rata_rating, COUNT(*) as jumlah_ulasan FROM rating WHERE buku_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$rating_result = $stmt->fetch(PDO::FETCH_ASSOC);
$rata_rating = round($rating_result['rata_rating'], 1);
$jumlah_ulasan = $rating_result['jumlah_ulasan'];

$query = "SELECT * FROM favorit WHERE buku_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$favorit_aktif = $stmt->rowCount() > 0;

$folder_uploads = '/CODINGAN/4-landingpageadmin/uploads/'; // Path utama penyimpanan gambar

$gambar_path = $folder_uploads . htmlspecialchars($buku['gambar']);

$default_gambar = $folder_uploads . 'default.jpg';

if (!empty($buku['gambar']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $gambar_path)) {
    $gambar = $gambar_path;
} else {
    $gambar = $default_gambar;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku - <?php echo htmlspecialchars($buku['judul']); ?></title>
    <link rel="stylesheet" href="detail_buku.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script>
        function tampilkanNotifikasi() {
            document.getElementById("notif-favorit").style.display = "block";
            setTimeout(() => {
                document.getElementById("notif-favorit").style.display = "none";
            }, 3000);
        }
    </script>
</head>

<body>
    <header>
        <div class="logo">
            <img src="../../logo.png" alt="Logo Perpus" srcset="" />
        </div>
        <nav class="navbar">
      <ul>
        <li>
          <a href="/CODINGAN/3-landingpageuser/beranda/beranda.html">Beranda</a>
        </li>
        <li>
          <a href="#">Katalog</a>
        </li>
        <li><a href="#">Aktivitas</a></li>
        <li>
          <a href="#">Favorit</a>
        </li>
        <li>
          <a href="/CODINGAN/3-landingpageuser/kontak/kontak.html">Kontak</a>
        </li>
        <li class="profil">
          <a href="#" class="akun"><i class="fas fa-user"></i></a>
        </li>
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
            <h1>Detail Buku</h1>
            <h2><?php echo htmlspecialchars($buku['judul']); ?></h2>
        </section>
        <section class="detail-buku">
            <img class="gambar-buku" src="<?php echo $gambar; ?>" alt="<?php echo htmlspecialchars($buku['judul']); ?>" width="200">
            <div class="informasi-buku">
                <p><strong>Penulis:</strong> <?php echo htmlspecialchars($buku['penulis']); ?></p>
                <p><strong>ISBN:</strong> <?php echo isset($buku['isbn']) ? htmlspecialchars($buku['isbn']) : '-'; ?></p>
                <p><strong>Tahun Terbit:</strong> <?php echo htmlspecialchars($buku['tahun_terbit']); ?></p>
                <p><strong>Jumlah Halaman:</strong> <?php echo htmlspecialchars($buku['jumlah_halaman']); ?></p>
                <p><strong>Kategori:</strong> <?php echo isset($buku['kategori_id']) ? htmlspecialchars($buku['nama_kategori']) : '-'; ?></p>
                <p><strong>Tipe Buku:</strong> <?php echo htmlspecialchars($buku['tipe_buku']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($buku['status']); ?></p>
                <p><strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($buku['deskripsi'])); ?></p>
            </div>
        </section>
        <section class="rating">
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
            <p>Tidak ada rating untuk buku ini.</p>
        <?php endif; ?>
    </div>
</section>

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
            <p>Belum ada ulasan untuk buku ini.</p>
            <a href="tambah_ulasan.php?id=<?= $buku_id ?>" class="btn-tambah-ulasan">Tambah Ulasan Sekarang</a>
        </div>
    <?php endif; ?>
</section>
        </section>
        <div class="action-buttons">
            <?php if ($buku['status'] === 'tersedia'): ?>
                <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/formpinjam/formku.php?buku_id=<?= $buku_id ?>" class="btn-pinjam">Pinjam Sekarang</a>
            <?php else: ?>
                <span class="btn-pinjam disabled">Buku Tidak Tersedia</span>
            <?php endif; ?>
            <form action="proses_favorit.php" method="POST" onsubmit="tampilkanNotifikasi()" style="display: inline-block;">
                <input type="hidden" name="buku_id" value="<?php echo $buku_id; ?>">
                <button type="submit" class="btn-favorit" <?php echo $favorit_aktif ? 'disabled' : ''; ?>>
                    <?php echo $favorit_aktif ? 'Sudah di Favorit' : 'Tambah ke Favorit'; ?>
                </button>
            </form>
        </div>
        <p id="notif-favorit" style="display: none; color: green;">Buku berhasil ditambahkan ke favorit Anda!</p>
    </main>
    <footer class="footer">
        <div class="container">
            <div class="left">
                <img
                    src="../../logo.png"
                    alt="Library of Riverhill Senior High School logo" />
                <p>
                    Lorem ipsum dolor sit amet consectetur adipiscing elit. Repudiandae
                    omnis molestias nobis. Lorem ipsum dolor sit amet consectetur
                    adipiscing elit. Repudiandae omnis molestias nobis.
                </p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="right">
                <h2>Tautan Fungsional</h2>
                <ul>
                    <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.html">Beranda</a></li>
                    <li><a href="/CODINGAN//3-landingpageuser/layanan/layanan.html">Layanan</a></li>
                    <li><a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a></li>
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