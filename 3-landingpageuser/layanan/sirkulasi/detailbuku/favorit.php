<?php
// Koneksi database
require_once 'formkoneksi.php';

try {
    // Ambil ID pengguna (user) dari sesi atau parameter lainnya
    session_start();
    $user_id = $_SESSION['user_id'] ?? 0; // Pastikan session sudah diatur

    // Query untuk mengambil data buku favorit
    $query_favorit = "
        SELECT 
            b.id AS buku_id,
            b.judul AS judul_buku,
            b.penulis AS penulis_buku,
            b.tahun_terbit AS tahun_terbit,
            b.gambar AS gambar_buku
        FROM favorit f
        JOIN buku b ON f.buku_id = b.id
        WHERE f.user_id = :user_id
    ";
    $stmt_favorit = $conn->prepare($query_favorit);
    $stmt_favorit->bindParam(':user_id', $user_id);
    $stmt_favorit->execute();
    $favorit = $stmt_favorit->fetchAll(PDO::FETCH_ASSOC);

    // Handle notifikasi dari proses_favorit.php
    $status = $_GET['status'] ?? '';
    $notif = '';
    if ($status === 'success') {
        $notif = '<p style="color: green; text-align: center;">Buku berhasil ditambahkan ke favorit!</p>';
    } elseif ($status === 'exists') {
        $notif = '<p style="color: red; text-align: center;">Buku sudah ada di daftar favorit!</p>';
    }

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Favorit</title>
  <link rel="stylesheet" href="favorit.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
</head>
<body>
  <header>
    <div class="logo">
      <img src="logo.png" alt="Logo Perpus" />
    </div>
    <nav class="navbar">
      <ul>
        <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.html">Beranda</a></li>
        <li><a href="#">Katalog</a></li>
        <li><a href="#">Aktivitas</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/favorit/favorit.php">Favorit</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.html">Kontak</a></li>
        <li class="profil"><a href="#" class="akun"><i class="fas fa-user"></i></a></li>
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
    <div class="intro-content">
      <h3>Panel Favorit</h3>
      <p>
        Di sini Anda dapat melihat daftar buku yang telah Anda tandai sebagai favorit.
      </p>
    </div>

    <!-- Notifikasi -->
    <?= $notif ?>

    <div class="isi">
      <!-- Daftar Buku Favorit -->
      <div class="panel-favorit">
        <?php if (!empty($favorit)): ?>
          <div class="favorit-list">
            <?php foreach ($favorit as $item): ?>
              <div class="favorit-item">
                <img src="<?= htmlspecialchars($item['gambar_buku']) ?>" alt="<?= htmlspecialchars($item['judul_buku']) ?>" />
                <h4><?= htmlspecialchars($item['judul_buku']) ?></h4>
                <p>Penulis: <?= htmlspecialchars($item['penulis_buku']) ?></p>
                <p>Tahun Terbit: <?= htmlspecialchars($item['tahun_terbit']) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="text-align: center;">Anda belum menambahkan buku ke daftar favorit.</p>
        <?php endif; ?>
      </div>
    </div>
  </main>
  <footer class="footer">
    <div class="container">
      <div class="left">
        <img src="logo.png" alt="Library of Riverhill Senior High School logo" />
        <p>
          Perpustakaan digital menyediakan akses mudah ke berbagai koleksi buku dan eBook untuk mendukung pembelajaran.
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
          <li><a href="/CODINGAN/3-landingpageuser/layanan/layanan.html">Layanan</a></li>
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