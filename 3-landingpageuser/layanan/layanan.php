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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>layanan</title>
    <link rel="stylesheet" href="layanan.css" />
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
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
      <div class="container">
        <div class="layout">
          <div class="text-section">
            <h1>
              APA SAJA LAYANAN YANG ADA DI <br />
              LIBRARY OF RIVENHILL SENIOR HIGH SCHOOL?
            </h1>
            <p>
              Kami menyediakan 3 layanan utama yang dapat diakses secara
              <em>offline</em> maupun <em>online</em>.
            </p>
          </div>
          <div class="services">
            <div class="service-card">
              <div class="icon"><i class="fas fa-book"></i></div>
              <h2>Sirkulasi</h2>
              <p>
                Layanan peminjaman dan pengembalian buku, baik dalam bentuk
                fisik maupun eBook, dengan sistem yang terintegrasi.
              </p>
              <a
                href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/katalog/katalog.php"
                class="btn-primary"
                >Lihat Selengkapnya</a
              >
            </div>
            <div class="service-card">
              <div class="icon"><i class="fas fa-book-open"></i></div>
              <h2>Referensi</h2>
              <p>
                Koleksi bahan referensi seperti ensiklopedia, jurnal, dan buku
                teks yang dapat digunakan di dalam perpustakaan.
              </p>
              <a href="/CODINGAN/3-landingpageuser/layanan/referensi/referensi.php" class="btn-primary">Lihat Selengkapnya</a>
            </div>
            <div class="service-card">
              <div class="icon"><i class="fas fa-archive"></i></div>
              <h2>Repository</h2>
              <p>
                Penyimpanan dan akses dokumen digital seperti skripsi, laporan
                penelitian, serta arsip akademik sekolah.
              </p>
              <a href="/CODINGAN/3-landingpageuser/layanan/repository/repository.php" class="btn-primary">Lihat Selengkapnya</a>
            </div>
          </div>
        </div>
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
