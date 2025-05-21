<?php
include 'formkoneksi.php';
session_start();
$user_id = $_SESSION['user_id'] ?? null;
if (!isset($_GET['id'])) {
    echo "ID buku tidak ditemukan!";
    exit;
}

$buku_id = $_GET['id'];

// Ambil data buku
$query = "SELECT * FROM buku WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buku) {
    echo "Buku tidak ditemukan!";
    exit;
}

// Ambil data rating
$query = "SELECT COALESCE(AVG(nilai), 0) as rata_rating, COUNT(*) as jumlah_ulasan FROM rating WHERE buku_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$rating_result = $stmt->fetch(PDO::FETCH_ASSOC);

$rata_rating = round($rating_result['rata_rating'], 1);
$jumlah_ulasan = $rating_result['jumlah_ulasan'];

// Ambil distribusi rating
$distribusi_query = "SELECT nilai AS rating, COUNT(*) AS count FROM rating WHERE buku_id = ? GROUP BY nilai";
$distribusi_stmt = $conn->prepare($distribusi_query);
$distribusi_stmt->execute([$buku_id]);
$distribusi_rating = $distribusi_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar ulasan
$ulasan_query = "SELECT r.id, r.anggota_id, r.buku_id, r.nilai, r.ulasan, u.username 
                 FROM rating r 
                 JOIN anggota u ON r.anggota_id = u.id 
                 WHERE r.buku_id = ?";
$ulasan_stmt = $conn->prepare($ulasan_query);
$ulasan_stmt->execute([$buku_id]);
$ulasan_list = $ulasan_stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah user sudah memberikan rating
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$existing_rating = null;
if ($user_id) {
    $check_rating_query = "SELECT * FROM rating WHERE buku_id = ? AND anggota_id = ?";
    $check_rating_stmt = $conn->prepare($check_rating_query);
    $check_rating_stmt->execute([$buku_id, $user_id]);
    $existing_rating = $check_rating_stmt->fetch(PDO::FETCH_ASSOC);
}

// Cek apakah buku sudah ditambahkan ke favorit
$query = "SELECT * FROM favorit WHERE buku_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id, $user_id]);
$favorit_aktif = $stmt->rowCount() > 0;

$folder_uploads = '/CODINGAN/4-landingpageadmin/uploads/';
$gambar_path = $folder_uploads . htmlspecialchars($buku['gambar']);
$default_gambar = $folder_uploads . 'default.jpg';
if (!empty($buku['gambar']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $gambar_path)) {
    $gambar = $gambar_path;
} else {
    $gambar = $default_gambar;
}

$status = $_GET['status'] ?? '';
$notif = '';
if ($status === 'success') {
    $notif = '<p style="color: green; text-align: center;">Buku berhasil ditambahkan ke favorit!</p>';
} elseif ($status === 'exists') {
    $notif = '<p style="color: red; text-align: center;">Buku sudah ada di daftar favorit!</p>';
} elseif ($status === 'removed') {
    $notif = '<p style="color: blue; text-align: center;">Buku berhasil dihapus dari favorit!</p>';
}
?>
<?= $notif ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku - <?php echo htmlspecialchars($buku['judul']); ?></title>
    <link rel="stylesheet" href="detail_buku.css">
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css " />
</head>
<body>
    <header>
        <div class="logo">
            <img src="../../logo.png" alt="Logo Perpus" srcset="" />
        </div>
        <nav class="navbar">
            <ul>
<li>
          <a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a>
        </li>
        <li>
          <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/katalog/katalog.php">Katalog</a>
        </li>
        <li><a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/aktivitas.php">Aktivitas</a></li>
        <li>
          <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php">Favorit</a>
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
                <p><strong>Kategori:</strong> <?php echo isset($buku['kategori']) ? htmlspecialchars($buku['kategori']) : '-'; ?></p>
                <p><strong>Tipe Buku:</strong> <?php echo htmlspecialchars($buku['tipe_buku']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($buku['status']); ?></p>
                <p><strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($buku['deskripsi'])); ?></p>
            </div>
        </section>
        
<!-- Rating Overview -->
<section class="rating">
    <div class="rating-container">
        <!-- Rating Overview -->
        <div class="rating-overview">
            <p><strong>Rating:</strong>
                ★ (<?= number_format($rata_rating, 1) ?>/5) <?= $jumlah_ulasan ?> ratings - <?= $jumlah_ulasan ?> reviews
            </p>
        </div>

<!-- Star Distribution -->
<div class="star-distribution">
    <?php for ($rating = 5; $rating >= 1; $rating--): ?>
        <?php
        // Cari jumlah ulasan untuk rating tertentu
        $count = 0;
        foreach ($distribusi_rating as $row) {
            if ($row['rating'] == $rating) {
                $count = $row['count'];
                break;
            }
        }

        // Hitung persentase untuk setiap rating
        $percentage = ($jumlah_ulasan > 0) ? ($count / $jumlah_ulasan) * 100 : 0;
        ?>
        <div class="rating-bar">
            <span><?= $rating ?> stars</span>
            <div class="bar-container">
                <div class="bar" style="width: <?= $percentage ?>%; background-color: #ffcc00;"></div>
            </div>
            <span><?= $count ?></span>
        </div>
    <?php endfor; ?>
</div>
    </div>

    <!-- User Reviews -->
<section class="user-reviews">
    <h3>Ulasan</h3>
    <p>Menampilkan <?= count($ulasan_list) > 0 ? '1 dari ' . $jumlah_ulasan : '0' ?> ulasan</p>

    <?php if (!empty($ulasan_list)): ?>
        <!-- Tampilkan hanya ulasan pertama -->
        <?php $first_review = $ulasan_list[0]; ?>
        <div class="review">
            <div class="review-header">
                <span><?= htmlspecialchars($first_review['username']) ?></span>
                <span>
                    <?php for ($i = 0; $i < $first_review['nilai']; $i++): ?>
                        ★
                    <?php endfor; ?>
                </span>
            </div>
            <p class="review-comment"><?= htmlspecialchars($first_review['ulasan']) ?></p>
        </div>

        <!-- Tombol Show More -->
        <?php if (count($ulasan_list) > 1): ?>
            <a href="lihat_semua_ulasan.php?id=<?= $buku_id ?>" class="show-more">Lihat Semua Ulasan ▼</a>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-reviews-container">
            <p>Belum ada ulasan untuk buku ini.</p>
        <?php endif; ?>
    </div>

        <?php if ($user_id && !$existing_rating): ?>
            <a href="tambah_ulasan.php?id=<?= $buku_id ?>" class="tambah-ulasan">Tambah Ulasan</a>
        <?php elseif ($user_id && $existing_rating): ?>
            <a href="edit_ulasan.php?id=<?= $existing_rating['id']?>" class="tambah-ulasan">Edit Ulasan</a>
        <?php else: ?>
            <p class="tambah-ulasan">Login untuk memberikan ulasan</p>
        <?php endif; ?>
</section>

        <!-- Tombol Tambah Ulasan atau Lihat Lebih Jauh -->
        
    </section>
</section>
        <div class="action-buttons">
            <?php if ($buku['status'] === 'tersedia'): ?>
                <?php if ($buku['tipe_buku'] === 'Buku Elektronik' && $buku['file_path']): ?>
                    <!-- Tombol untuk Ebook -->
                    <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/formpinjam/view_pdf.php?file=<?= urlencode(basename($buku['file_path'])) ?>"
                        target="_blank"
                        class="btn-pinjam">Lihat Sekarang</a>
                <?php else: ?>
                    <!-- Tombol untuk Buku Fisik -->
                    <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/formpinjam/formku.php?buku_id=<?= $buku_id ?>"
                        class="btn-pinjam">Pinjam Sekarang</a>
                <?php endif; ?>
            <?php else: ?>
                <span class="btn-pinjam disabled">Buku Tidak Tersedia</span>
            <?php endif; ?>
            <!-- Tombol Tambah ke Favorit -->
            <form action="proses_favorit.php" method="POST" style="display: inline-block;">
                <input type="hidden" name="buku_id" value="<?= $buku_id ?>">
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
        <p id="notif-favorit" style="display: none; color: green;">Buku berhasil ditambahkan ke favorit Anda!</p>
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