<?php
include '../formkoneksi.php';

// Cek apakah user sudah login
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Harus login dulu!");
}

$user_id = $_SESSION['user_id'];

// Ambil ID dokumen dari URL
$dokumen_id = $_GET['id'] ?? null;

// Ambil data dokumen
$stmt = $conn->prepare("SELECT * FROM dokumen WHERE id = ?");
$stmt->execute([$dokumen_id]);
$dokumen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dokumen) {
    die("Dokumen tidak ditemukan!");
}

// Ambil rating & ulasan dokumen
$dokumen_id = $_GET['id'] ?? null;
if (!$dokumen_id) die("ID dokumen tidak ditemukan!");

// Ambil rata-rata rating dan jumlah ulasan (khusus dokumen)
$stmt = $conn->prepare("SELECT COALESCE(AVG(nilai), 0) as rata_rating, COUNT(*) as jumlah_ulasan FROM rating WHERE dokumen_id = ?");
$stmt->execute([$dokumen_id]);
$rating_result = $stmt->fetch(PDO::FETCH_ASSOC);
$rata_rating = round($rating_result['rata_rating'], 1);
$jumlah_ulasan = $rating_result['jumlah_ulasan'];

// Ambil distribusi rating
$distribusi_query = "SELECT nilai AS rating, COUNT(*) AS count FROM rating WHERE dokumen_id = ? GROUP BY nilai";
$distribusi_stmt = $conn->prepare($distribusi_query);
$distribusi_stmt->execute([$dokumen_id]);
$distribusi_rating = $distribusi_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar ulasan
$ulasan_query = "SELECT r.id, r.anggota_id, r.dokumen_id, r.nilai, r.ulasan, u.username 
                 FROM rating r 
                 JOIN anggota u ON r.anggota_id = u.id 
                 WHERE r.dokumen_id = ? ORDER BY r.created_at DESC";
$ulasan_stmt = $conn->prepare($ulasan_query);
$ulasan_stmt->execute([$dokumen_id]);
$ulasan_list = $ulasan_stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah user sudah memberikan rating
$existing_rating = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_rating_query = "SELECT * FROM rating WHERE dokumen_id = ? AND anggota_id = ?";
    $check_rating_stmt = $conn->prepare($check_rating_query);
    $check_rating_stmt->execute([$dokumen_id, $user_id]);
    $existing_rating = $check_rating_stmt->fetch(PDO::FETCH_ASSOC);
}

// Cek apakah dokumen sudah ditambahkan ke favorit
$query = "SELECT * FROM favorit WHERE dokumen_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$dokumen_id, $user_id]);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
</head>
<body>
    <header>
    <div class="logo">
        <img src="../../logo.png" alt="Logo Perpus" srcset="" />
    </div>
    <nav class="navbar">
        <ul>
            <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
            <li><a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/aktivitas.php">Aktivitas</a></li>
            <li><a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php">Favorit</a></li>
            <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.php">Kontak</a></li>
            <li class="profil"><a href="/CODINGAN/3-landingpageuser/akun/akun.php" class="akun"><i class="fas fa-user"></i></a></li>
            <li>
                <button class="btn-logout">
                    <i class="fas fa-arrow-left"></i>
                    <a href="<?= 
                        ($dokumen['tipe_dokumen'] === 'jurnal' || 
                         $dokumen['tipe_dokumen'] === 'artikel_konferensi' || 
                         $dokumen['tipe_dokumen'] === 'modul_pelajaran') 
                        ? '/CODINGAN/3-landingpageuser/layanan/referensi/referensi.php' 
                        : '/CODINGAN/3-landingpageuser/layanan/repository/repository.php' 
                    ?>">Kembali</a>
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
            <div class="detail-dokumen-box">
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
            </div>
        </section>
        <!-- Rating Overview -->
        <section class="rating">
            <div class="rating-container">
                <div class="rating-overview">
                    <p>
                        <strong>Rating:</strong>
                        ★ (<?= number_format($rata_rating, 1) ?>/5) <?= $jumlah_ulasan ?> ratings - <?= $jumlah_ulasan ?> reviews
                    </p>
                </div>
                <div class="star-distribution">
                    <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                        <?php
                        $count = 0;
                        foreach ($distribusi_rating as $row) {
                            if ($row['rating'] == $rating) {
                                $count = $row['count'];
                                break;
                            }
                        }
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

            <section class="user-reviews">
                <h3>Ulasan</h3>
                <p>Menampilkan <?= count($ulasan_list) > 0 ? '1 dari ' . $jumlah_ulasan : '0' ?> ulasan</p>
                <?php if (!empty($ulasan_list)): ?>
                    <?php $first_review = $ulasan_list[0]; ?>
                    <div class="review">
                        <div class="review-header">
                            <span><?= htmlspecialchars($first_review['username']) ?></span>
                            <span>
                                <?php for ($i = 0; $i < $first_review['nilai']; $i++): ?>★<?php endfor; ?>
                            </span>
                        </div>
                        <p class="review-comment"><?= htmlspecialchars($first_review['ulasan']) ?></p>
                    </div>
                    <?php if (count($ulasan_list) > 1): ?>
                        <a href="/CODINGAN/3-landingpageuser/layanan/referensi/detail/lihatulrate.php?id=<?= $dokumen_id ?>" class="show-more">Lihat Semua Ulasan ▼</a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-reviews-container">
                        <p>Belum ada ulasan untuk buku ini.</p>
                    </div>
                <?php endif; ?>

                <?php if ($user_id && !$existing_rating): ?>
                    <a href="/CODINGAN/3-landingpageuser/layanan/referensi/detail/tambahulrate.php?id=<?= $dokumen_id ?>" class="tambah-ulasan">Tambah Ulasan</a>
                <?php elseif ($user_id && $existing_rating): ?>
                    <a href="/CODINGAN/3-landingpageuser/layanan/referensi/detail/editulrate.php?id=<?= $existing_rating['id'] ?>" class="tambah-ulasan">Edit Ulasan</a>
                <?php else: ?>
                    <p class="tambah-ulasan">Login untuk memberikan ulasan</p>
                <?php endif; ?>
            </section>
        </section>
        
        <div class="action-buttons">
            <?php if ($dokumen['status'] === 'tersedia' && !empty($dokumen['file_path'])): ?>
                <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/formpinjam/view_pdf.php?file=<?= urlencode(basename($dokumen['file_path'])) ?>" target="_blank" class="btn-pinjam">Lihat Sekarang</a>
            <?php else: ?>
                <span class="btn-pinjam disabled">Dokumen Tidak Tersedia</span>
            <?php endif; ?>
            <form action="prosesfav.php" method="POST" style="display: inline-block;">
                <input type="hidden" name="dokumen_id" value="<?= $dokumen_id ?>">
                <?php if ($favorit_aktif): ?>
                    <input type="hidden" name="action" value="hapus">
                    <button type="submit" class="btn-favorit btn-hapus">Hapus dari Favorit</button>
                <?php else: ?>
                    <input type="hidden" name="action" value="tambah">
                    <button type="submit" class="btn-favorit btn-tambah">Tambah ke Favorit</button>
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
          Perpustakaan SMA Rivenhill berkomitmen menjadi pusat pembelajaran yang mendukung visi sekolah dalam menciptakan generasi berwawasan luas. Kami buka setiap hari Senin-Jumat pukul 07.30-15.30 WIB.
        </p>
        <div class="social-icons">
          <a href="https://wa.me/6285936164597" target="_blank"><i class="fab fa-whatsapp"></i></a>
          <a href="https://www.linkedin.com/in/syarivatun-nisa-i-nur-aulia-3ab52b2bb/" target="_blank"><i class="fab fa-linkedin"></i></a>
          <a href="https://instagram.com/jeonwpnwoo" target="_blank"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
      <div class="right">
        <h2>Tautan Fungsional</h2>
        <ul>
          <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
          <li><a href="/CODINGAN/3-landingpageuser/layanan/layanan.php">Layanan</a></li>
          <li><a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      Copyright © 2024 Library of Riverhill Senior High School. All Rights Reserved
    </div>
  </footer>
</body>
</html>