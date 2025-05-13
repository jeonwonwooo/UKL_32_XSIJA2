<?php
include 'formkoneksi.php';

// Ambil parameter GET
$kategori = $_GET['kategori'] ?? 'all';
$kata_kunci = $_GET['q'] ?? '';

// Filter kategori
$filter_kategori = "";
if ($kategori != 'all') {
    $filter_kategori = " AND tipe_dokumen = '$kategori'";
}

// Filter pencarian
$filter_pencarian = "";
if (!empty($kata_kunci)) {
    $filter_pencarian = " AND (judul LIKE '%$kata_kunci%' OR penulis LIKE '%$kata_kunci%')";
}

// Query utama
$query = "SELECT * FROM dokumen WHERE status = 'tersedia' $filter_kategori $filter_pencarian ORDER BY created_at DESC";

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Eksekusi query utama
$stmt = $conn->query($query . " LIMIT $start, $limit");
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total data untuk pagination
$total_stmt = $conn->query("SELECT COUNT(*) as total FROM dokumen WHERE status = 'tersedia' $filter_kategori $filter_pencarian");
$total_data = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_data / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Repository</title>
  <link rel="stylesheet" href="repository.css">
  <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css " />
</head>
<body>

<!-- Header -->
<header>
  <div class="logo">
    <img src="logo.png" alt="Logo Perpus" />
  </div>
  <nav class="navbar">
    <ul>
      <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.html">Beranda</a></li>
      <li><a href="#">Katalog</a></li>
      <li><a href="#">Aktivitas</a></li>
      <li><a href="#">Favorit</a></li>
      <li><a href="#">Kontak</a></li>
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

<!-- Hero Section -->
<section class="hero">
  <h1>REPOSITORY</h1>
  <p>Temukan dokumen SMA Rivenhill yang kamu butuhkan. Pastikan dokumen yang kamu cari tersedia</p>
</section>

<!-- Filter Kategori -->
<div class="filter">
  <form method="GET" action="">
    <div class="filter-container">
      <div class="filter-dropdown">
        <select name="kategori" class="filter-select">
          <option value="all" <?= $kategori == 'all' ? 'selected' : '' ?>>Semua</option>
          <option value="karya_murid" <?= $kategori == 'karya_murid' ? 'selected' : '' ?>>Karya Siswa/I</option>
          <option value="tugas_akhir" <?= $kategori == 'tugas_akhir' ? 'selected' : '' ?>>Tugas Akhir</option>
          <option value="makalah" <?= $kategori == 'makalah' ? 'selected' : '' ?>>Makalah</option>
          <option value="laporan" <?= $kategori == 'laporan' ? 'selected' : '' ?>>Laporan</option>
          <option value="lainnya" <?= $kategori == 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
        </select>
        <input type="text" name="q" value="<?= htmlspecialchars($kata_kunci) ?>" hidden>
        <button type="submit" class="filter-button">Filter</button>
      </div>
    </div>
  </form>
</div>

<!-- Daftar Dokumen -->
<section class="documents">
  <?php if (!empty($result)): ?>
    <?php foreach ($result as $row): ?>
      <div class="document-card">
        <img src="images/document-icon.png" alt="Icon Dokumen">
        <h3><?= htmlspecialchars($row['judul']) ?></h3>
        <p><strong>Penulis:</strong> <?= htmlspecialchars($row['penulis']) ?></p>
        <p><strong>Tahun:</strong> <?= $row['tahun_terbit'] ?></p>
        <p><small>Jenis: <?= ucfirst(str_replace('_', ' ', $row['tipe_dokumen'])) ?></small></p>
        <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank">📄 Baca Sekarang</a>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="text-align:center;">Tidak ada dokumen ditemukan.</p>
  <?php endif; ?>
</section>

<!-- Footer -->
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
          <a href="https://wa.me/6285936164597" target="_blank"><i class="fab fa-whatsapp"></i></a>
          <a href="https://www.linkedin.com/in/syarivatun-nisa-i-nur-aulia-3ab52b2bb/" target="_blank"><i
              class="fab fa-linkedin"></i></a>
          <a href="https://instagram.com/jeonwpnwoo" target="_blank"><i class="fab fa-instagram"></i></a>
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