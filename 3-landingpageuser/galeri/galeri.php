<?php
include 'formkoneksi.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?error=haruslogindulu.");
    exit();
}

$filter = $_GET['filter'] ?? 'terbaru';

if ($filter === 'terbaru') {
  $stmt = $conn->prepare("
        SELECT id, judul, konten, gambar, tanggal_publikasi
        FROM artikel
        WHERE status = 'published'
        ORDER BY created_at DESC
    ");
} elseif ($filter === 'abjad') {
  $stmt = $conn->prepare("
        SELECT id, judul, konten, gambar, tanggal_publikasi
        FROM artikel
        WHERE status = 'published'
        ORDER BY judul ASC
    ");
} else {
  $stmt = $conn->prepare("
        SELECT id, judul, konten, gambar, tanggal_publikasi
        FROM artikel
        WHERE status = 'published'
        ORDER BY created_at DESC
    ");
}

$stmt->execute();
$artikel = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galeri Artikel</title>
  <link rel="stylesheet" href="galeri.css">
  <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>

<body>
  <header>
      <div class="logo">
        <img src="logo.png" alt="Logo Perpus" srcset="" />
      </div>
      <nav class="navbar">
        <ul>
          <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/profil/umum/profil.php">Tentang</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/layanan/layanan.php">Layanan</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.php">Kontak</a></li>
        <li class="profil"><a href="/CODINGAN/3-landingpageuser/akun/akun.php" class="akun"><i class="fas fa-user"></i></a></li>
          <li>
            <button class="btn-logout">
              <i class="fas fa-arrow-left"></i>
              <a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Kembali</a>
            </button>
          </li>
        </ul>
      </nav>
    </header>
  <main>
    <section class="get2">
      <div class="get2-content">
        <h1>Ingin Tahu Kegiatan Kami?</h1>
        <p>Ayo gulir lebih jauh!</p>
      </div>
    </section>
    <div class="filter-wrapper">
      <div class="filter-dropdown">
        <button class="filter-btn">Pilih Filter ▼</button>
        <div class="filter-content">
          <a href="?filter=terbaru">Terbaru</a>
          <a href="?filter=abjad">Abjad</a>
          <a href="?filter=semua">Semua</a>
        </div>
      </div>
    </div>
    <div class="row">
      <?php if (count($artikel) > 0): ?>
        <?php foreach ($artikel as $row): ?>
          <div class="col-md-4 mb-4">
            <div class="card">
              <img src="/CODINGAN/4-landingpageadmin/uploads/<?= htmlspecialchars($row['gambar']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['judul']) ?>">
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($row['judul']) ?></h5>
                <p class="card-text"><?= substr(htmlspecialchars($row['konten']), 0, 100) ?>...</p>
                <a href="detail_artikel.php?id=<?= $row['id'] ?>" class="btn btn-primary">Baca Selengkapnya</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <p>Tidak ada artikel yang tersedia.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <div class="left">
        <img src="logo.png" alt="Library of Riverhill Senior High School logo" />
        <p>
          Perpustakaan SMA Rivenhill didukung oleh tim profesional yang berdedikasi untuk memberikan layanan terbaik. Kami buka Senin-Jumat pukul 07.30-15.30 WIB, siap membantu kebutuhan informasi dan literasi seluruh warga sekolah.
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