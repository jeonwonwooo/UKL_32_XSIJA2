<?php
include 'formkoneksi.php'; 

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?error=haruslogindulu.");
    exit();
}

$kategori = $_GET['kategori'] ?? 'all';
$kata_kunci = $_GET['q'] ?? '';

$tipe_repository = ['karya_murid', 'tugas_akhir', 'makalah', 'laporan'];

$filter_kategori = "";
if ($kategori != 'all' && in_array($kategori, $tipe_repository)) {
  $filter_kategori = " AND tipe_dokumen = '$kategori'";
} else if ($kategori == 'all') {
  $filter_kategori = " AND tipe_dokumen IN ('" . implode("','", $tipe_repository) . "')";
}

$filter_pencarian = "";
if (!empty($kata_kunci)) {
  $filter_pencarian = " AND (judul LIKE '%$kata_kunci%' OR penulis LIKE '%$kata_kunci%')";
}

$query = "SELECT * FROM dokumen WHERE status = 'tersedia' $filter_kategori $filter_pencarian ORDER BY created_at DESC";

$limit = 6;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $limit;

$stmt = $conn->query($query . " LIMIT $start, $limit");
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_stmt = $conn->query("SELECT COUNT(*) as total FROM dokumen WHERE status = 'tersedia' $filter_kategori $filter_pencarian");
$total_data = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_data / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Referensi Akademik</title>
  <link rel="stylesheet" href="repository.css">
  <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css " />
</head>

<body>

  <header>
    <div class="logo">
      <img src="logo.png" alt="Logo Perpus" srcset="" />
    </div>
    <nav class="navbar">
            <ul>
                <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/layanan/repository/repository.php">Katalog</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/aktivitas.php">Aktivitas</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php">Favorit</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.php">Kontak</a></li>
                <li class="profil"><a href="/CODINGAN/3-landingpageuser/akun/akun.php" class="akun"><i class="fas fa-user"></i></a></li>
                <li>
                    <button class="btn-logout">
                        <i class="fas fa-arrow-left"></i>
                        <a href="/CODINGAN/3-landingpageuser/layanan/layanan.php">Kembali</a>
                    </button>
                </li>
            </ul>
        </nav>
  </header>

  <section class="hero">
  <h1>REPOSITORY</h1>
  <p>Temukan berbagai dokumen akademik milik SMA Rivenhill di sini</p>
  </section>

  <div class="filter">
  <form method="GET" action="">
    <div class="filter-container">
    <div class="filter-dropdown">
      <select name="kategori" class="filter-select">
      <option value="all" <?= $kategori == 'all' ? 'selected' : '' ?>>Semua</option>
      <option value="karya_murid" <?= $kategori == 'karya_murid' ? 'selected' : '' ?>>Karya Siswa/i</option>
      <option value="tugas_akhir" <?= $kategori == 'tugas_akhir' ? 'selected' : '' ?>>Tugas Akhir</option>
      <option value="makalah" <?= $kategori == 'makalah' ? 'selected' : '' ?>>Makalah</option>
      <option value="laporan" <?= $kategori == 'laporan' ? 'selected' : '' ?>>Laporan</option>
      </select>
      <input type="text" name="q" value="<?= htmlspecialchars($kata_kunci) ?>" hidden>
      <button type="submit" class="filter-button">Filter</button>
    </div>
    </div>
  </form>
  </div>

  <section class="documents">
  <?php if (!empty($result)): ?>
    <?php foreach ($result as $row): ?>
    <div class="document-card">
      <div class="document-icon">
      <i class="fa-solid fa-file-lines"></i>
      </div>
      <h3><?= htmlspecialchars($row['judul']) ?></h3>
      <p><strong>Penulis:</strong> <?= htmlspecialchars($row['penulis']) ?></p>
      <p><strong>Tahun:</strong> <?= $row['tahun_terbit'] ?></p>
      <p><strong>Jenis:</strong> <?= ucfirst(str_replace('_', ' ', $row['tipe_dokumen'])) ?></p>
      <a class="read-btn" href="/CODINGAN/3-landingpageuser/layanan/referensi/detail/detail_dokumen.php?id=<?= htmlspecialchars($row['id']) ?>">📄 Lihat Detail</a>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="text-align:center;">Tidak ada dokumen ditemukan.</p>
  <?php endif; ?>
  </section>

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
