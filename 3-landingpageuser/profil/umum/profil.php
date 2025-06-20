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
    <title>The Profile</title>
    <link rel="stylesheet" href="profil.css" />
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
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
          <h1>Ingin Mengetahui Kami Lebih Dalam?</h1>
          <p>Ayo gulir lebih jauh!</p>
        </div>
      </section>
      <section class="sejarah">
        <h2>Sejarah</h2>
        <div class="sejarah-content">
          <div class="sej-text">
            <p>
              Situs ini dikembangkan sejak 2024 sebagai bagian dari komitmen sekolah dalam mendukung pendidikan berkualitas. Kami terus berinovasi untuk memenuhi kebutuhan literasi siswa dan guru, termasuk transformasi digital.
            </p>
          </div>
          <div class="sej-img">
            <img src="gambar5.jpg" alt="Sejarah" srcset="" />
          </div>
        </div>
      </section>
      <section class="visimisi">
        <h2>Visi Misi</h2>
        <div class="visi">
          <div class="visi-content">
            <h4>VISI</h4>
            <p>
              Menjadi pusat sumber belajar yang inspiratif dan inovatif bagi komunitas SMA Rivenhill, mendorong budaya literasi, serta mendukung pengembangan potensi akademik dan karakter siswa melalui layanan informasi yang berkualitas.
            </p>
          </div>
          <div class="misi-content">
            <h4>MISI</h4>
            <p>
              1. Menyediakan koleksi bahan pustaka yang relevan dan mutakhir<br>
              2. Menciptakan lingkungan belajar yang nyaman dan kondusif<br>
              3. Menerapkan teknologi informasi untuk kemudahan akses<br>
            </p>
          </div>
        </div>
      </section>
      <section class="pengelola">
        <h2>Pengelola</h2>
        <p>
          Perpustakaan kami dikelola oleh tim profesional yang terdiri dari kepala perpustakaan & 3 petugas. Kami siap memberikan pelayanan terbaik dengan pendekatan yang ramah dan solutif untuk kebutuhan informasi Anda.
        </p>
        <button class="sapa">
          <a href="/CODINGAN/3-landingpageuser/profil/petugas/petugas.php"
            >Mari Saling Menyapa!</a
          >
        </button>
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