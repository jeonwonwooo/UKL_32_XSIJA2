<?php
include 'formkoneksi.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?error=haruslogindulu.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library of Riverhill Senior High School</title>
  <link rel="stylesheet" href="beranda.css">
  <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <header>
    <div class="logo">
      <img src="logo.png" alt="Logo Perpus" />
    </div>
    <nav class="navbar">
      <ul>
        <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/profil/umum/profil.php">Tentang</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/layanan/layanan.php">Layanan</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a></li>
        <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.php">Kontak</a></li>
        <li class="profil">
          <a href="/CODINGAN/3-landingpageuser/akun/akun.php" class="akun">
            <i class="fas fa-user"></i>
          </a>
        </li>
        <li>
          <button class="btn-logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            <a href="/CODINGAN/z-yakinlogout/formyakin.php">Logout</a>
          </button>
        </li>
      </ul>
    </nav>
  </header>

  <?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] === 'success_admin'): ?>
      <p style="color: blue; text-align: center;">Terima kasih telah kembali, Admin.</p>
    <?php elseif ($_GET['status'] === 'balik_anggota'): ?>
      <p style="color: blue; text-align: center;">Terima kasih telah kembali.</p>
    <?php elseif ($_GET['status'] === 'ga_kenal'): ?>
      <p style="color: red; text-align: center;">Akun tidak dikenali!</p>
    <?php endif; ?>
  <?php endif; ?>

  <main>
    <section class="hero">
      <div class="hero-content">
        <h1>Selamat Datang di</h1>
        <h5><i>Portal Perpustakaan SMA Rivenhill</i></h5>
        <br />
        <p>
          Perpustakaan SMA Rivenhill menyediakan berbagai koleksi buku berkualitas untuk mendukung pembelajaran siswa. Dengan fasilitas modern dan suasana nyaman, kami berkomitmen menjadi pusat informasi dan pengetahuan bagi seluruh warga sekolah. Mari jelajahi dunia melalui halaman-halaman buku bersama kami.
        </p>
      </div>
    </section>

    <section class="intro">
      <div class="gambar2">
        <img src="gambar2.jpg" alt="Gambar Perpus" />
      </div>
      <div class="intro-content">
        <h3>Kami Adalah</h3>
        <h6>Perpustakaan SMA Rivenhill</h6>
        <p>
          Sebagai jantung pengetahuan sekolah, kami menyediakan berbagai koleksi buku berkualitas untuk membantu siswa dalam memahami ilmu. Tim pustakawan kami siap membantu Anda menemukan referensi yang dibutuhkan untuk tugas sekolah maupun pengembangan pribadi.
        </p>
      </div>
    </section>

    <section class="video">
      <div class="video-content">
        <h3>Video Profil</h3>
        <p>Perpustakaan SMA Rivenhill</p>
        <video width="560" height="315" controls>
          <source src="videoukl.mp4" type="video/mp4" />
          Your browser does not support the video tag.
        </video>
      </div>
    </section>

    <section class="layanan">
      <div class="layanan-content">
        <h3>Layanan Perpustakaan</h3>
        <h6>Melayani dengan Senyuman, Mengutamakan Kepuasan</h6>
        <div class="layanan-item">
          <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/katalog/katalog.php" class="card-link">
            <div class="card">
              <div class="card-icon">
                <i class="fas fa-book"></i>
              </div>
              <div class="card-content">
                <h4>Sirkulasi</h4>
                <p>
                  Layanan peminjaman dan pengembalian buku dengan sistem yang mudah bagi pengguna dan cepat. Setiap anggota dapat meminjam berbagai macam buku dengan durasi peminjaman selama 7 hari. 
                </p>
              </div>
            </div>
          </a>
          <a href="/CODINGAN/3-landingpageuser/layanan/referensi/referensi.php" class="card-link">
            <div class="card">
              <div class="card-icon">
                <i class="fas fa-book-open"></i>
              </div>
              <div class="card-content">
                <h4>Referensi</h4>
                <p>
                  Koleksi referensi kami meliputi ensiklopedia, kamus, buku statistik, dan bahan penelitian lainnya yang dapat diakses di tempat. Kami juga menyediakan akses ke jurnal online dan database akademik terpercaya.
                </p>
              </div>
            </div>
          </a>
          <a href="/CODINGAN/3-landingpageuser/layanan/repository/repository.php" class="card-link">
            <div class="card">
              <div class="card-icon">
                <i class="fas fa-book-reader"></i>
              </div>
              <div class="card-content">
                <h4>Repository</h4>
                <p>
                  Arsip digital karya siswa dan guru SMA Rivenhill, termasuk skripsi, laporan penelitian, dan karya tulis lainnya. Repository kami menjadi sumber inspirasi dan referensi untuk pengembangan akademik sekolah.
                </p>
              </div>
            </div>
          </a>
        </div>
      </div>
      <div class="struktur">
        <h3>Struktur Organisasi dan Petugas</h3>
        <p>Perpustakaan SMA Rivenhill</p>
        <button class="see">
          <a href="/CODINGAN//3-landingpageuser/profil/petugas/petugas.php">Lihat di Sini</a>
        </button>
      </div>
    </section>
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