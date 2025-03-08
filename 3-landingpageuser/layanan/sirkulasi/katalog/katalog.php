<?php include 'formkoneksi.php'; ?>

<?php
// Ambil parameter filter dan search dari URL
$filter = $_GET['filter'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Query dasar
$query = "
    SELECT buku.id, buku.judul, buku.penulis, buku.tahun_terbit, buku.gambar, buku.status, kategori.nama_kategori
    FROM buku
    JOIN kategori ON buku.kategori_id = kategori.id
";

// Tambahkan kondisi filter
if ($filter === 'tersedia') {
  $query .= " WHERE buku.status = 'tersedia'";
} elseif ($filter === 'dipinjam') {
  $query .= " WHERE buku.status = 'dipinjam'";
} elseif ($filter === 'habis') {
  $query .= " WHERE buku.status = 'habis'";
}

// Tambahkan kondisi pencarian
if (!empty($search)) {
  if (strpos($query, 'WHERE') !== false) {
    $query .= " AND (buku.judul LIKE :search OR buku.penulis LIKE :search)";
  } else {
    $query .= " WHERE (buku.judul LIKE :search OR buku.penulis LIKE :search)";
  }
}

// Persiapkan statement
$stmt = $conn->prepare($query);

// Bind parameter pencarian jika ada
if (!empty($search)) {
  $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}

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
          <a href="/CODINGAN/3-landingpageuser/profil/umum/profil.html">Profil</a>
        </li>
        <li><a href="/CODINGAN//3-landingpageuser/layanan/layanan.html">Layanan</a></li>
        <li>
          <a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a>
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
      <form action="" method="GET" class="search-bar">
        <div class="search-container">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Cari judul, penulis, atau kategori..." value="<?= htmlspecialchars($search) ?>">
          <button type="submit">Cari</button>
        </div>
      </form>
    </section>
    <section class="filter">
      <div class="filter-container">
        <div class="filter-dropdown">
          <button class="filter-button">
            <i class="fas fa-filter"></i> Filter
          </button>
          <div class="dropdown-content">
            <a href="?filter=semua" class="<?= $filter === 'semua' ? 'active' : '' ?>">Semua</a>
            <a href="?filter=fisik" class="<?= $filter === 'fisik' ? 'active' : '' ?>">Fisik</a>
      <a href="?filter=ebook" class="<?= $filter === 'ebook' ? 'active' : '' ?>">Ebook</a>
            <a href="?filter=tersedia" class="<?= $filter === 'tersedia' ? 'active' : '' ?>">Tersedia</a>
            <a href="?filter=dipinjam" class="<?= $filter === 'dipinjam' ? 'active' : '' ?>">Dipinjam</a>
            <a href="?filter=habis" class="<?= $filter === 'habis' ? 'active' : '' ?>">Habis</a>
          </div>
        </div>
      </div>
    </section>
      <section class="book-list">
        <?php foreach ($buku as $row): ?>
          <div class="book-item">
            <img src="/CODINGAN/4-landingpageadmin/uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>" class="book-image">
            <div class="book-info">
              <div class="book-title"><?= htmlspecialchars($row['judul']) ?></div>
              <div class="book-actions">
                <a href="#" class="btn">Pinjam</a>
                <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/detail_buku.php?id=<?= $row['id'] ?>" class="btn">Detail</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
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