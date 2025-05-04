<?php include 'formkoneksi.php'; ?>

<?php
$filter = $_GET['filter'] ?? 'semua';

$query = "
    SELECT buku.id, buku.judul, buku.penulis, buku.tahun_terbit, buku.gambar, buku.status, kategori.nama_kategori, buku.tipe_buku
    FROM buku
    JOIN kategori ON buku.kategori_id = kategori.id
";

if ($filter === 'fisik') {
    $query .= " WHERE buku.tipe_buku = 'Buku Fisik'";
} elseif ($filter === 'ebook') {
    $query .= " WHERE buku.tipe_buku = 'Buku Elektronik'";
}

$stmt = $conn->prepare($query);
$stmt->execute();
$buku = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Katalog Buku</title>
  <link rel="stylesheet" href="katalog.css">
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
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
    <section class="header">
      <h1>Katalog Buku</h1>
      <p>
        Selamat datang di perpustakaan sekolah Rivenhill! Temukan berbagai koleksi buku menarik yang dapat kamu pinjam.
        Jelajahi pengetahuan dan inspirasi melalui buku-buku terbaik kami.
      </p>
    </section>

    <!-- Filter Dropdown -->
    <section class="filter">
      <div class="filter-container">
        <div class="filter-dropdown">
          <button class="filter-button">
            <i class="fas fa-filter"></i> Filter 
          </button>
          <div class="dropdown-content">
            <a href="?filter=semua" class="<?= $filter === 'semua' ? 'active' : '' ?>">Semua</a>
            <a href="?filter=fisik" class="<?= $filter === 'fisik' ? 'active' : '' ?>">Buku Fisik</a>
            <a href="?filter=ebook" class="<?= $filter === 'ebook' ? 'active' : '' ?>">Buku Elektronik</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Daftar Buku -->
    <section class="book-list" id="book-list">
      <?php if (empty($buku)): ?>
        <p>Tidak ada buku yang ditemukan.</p>
      <?php else: ?>
        <?php foreach ($buku as $row): ?>
          <div class="book-item" data-title="<?= htmlspecialchars(strtolower($row['judul'])) ?>" 
               data-author="<?= htmlspecialchars(strtolower($row['penulis'])) ?>" 
               data-category="<?= htmlspecialchars(strtolower($row['nama_kategori'])) ?>">
            <img src="/CODINGAN/4-landingpageadmin/uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>" class="book-image">
            <div class="book-info">
              <div class="book-title"><?= htmlspecialchars($row['judul']) ?></div>
              <div class="book-actions">
                <?php if ($row['status'] === 'tersedia'): ?>
                  <?php if ($row['tipe_buku'] === 'fisik'): ?>
                    <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/formpinjam/formku.php?buku_id=<?= $row['id'] ?>" class="btn">Pinjam</a>
                  <?php elseif ($row['tipe_buku'] === 'ebook'): ?>
                    <a href="/CODINGAN/uploads/<?= htmlspecialchars($row['file_path']) ?>" download class="btn">Download</a>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="btn disabled">Tidak Tersedia</span>
                <?php endif; ?>
                <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/detail_buku.php?id=<?= $row['id'] ?>" class="btn">Detail</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
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
            <a href="https://wa.me/6285936164597" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <a href="https://www.linkedin.com/in/syarivatun-nisa-i-nur-aulia-3ab52b2bb/" target="_blank"><i class="fab fa-linkedin"></i></a>
            <a href="https://instagram.com/jeonwpnwoo" target="_blank"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
        <div class="right">
          <h2>Tautan Fungsional</h2>
          <ul>
            <li><a href="beranda.html">Beranda</a></li>
            <li><a href="/CODINGAN//3-landingpageuser/layanan/layanan.html">Layanan</a></li>
            <li><a href="/CODINGAN//3-landingpageuser/galeri/galeri.html">Galeri</a></li>
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