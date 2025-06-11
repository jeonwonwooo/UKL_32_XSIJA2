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
    <link rel="stylesheet" href="petugas.css" />
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  </head>
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
              <a href="/CODINGAN/3-landingpageuser/profil/umum/profil.php">Kembali</a>
            </button>
          </li>
        </ul>
      </nav>
    </header>

    <main>
      <section class="pustakawan">
        <h2>PUSTAKAWAN</h2>
        <div class="pustakawan-container">
          <a
            href="/CODINGAN/3-landingpageuser/profil/petugas/pustakawan/pustakawan.html#wonwoo"
            class="card">
            <img src="wonwoo.jpeg" alt="Foto Wonwoo" />
            <div class="card-text">
              <h4>Wonwoo Jeon</h4>
              <h6>Layanan Pemustaka</h6>
            </div>
          </a>
          <a
            href="/CODINGAN/3-landingpageuser/profil/petugas/pustakawan/pustakawan.html#joshua"
            class="card">
            <img src="joshua.jpeg" alt="Foto Joshua" />
            <div class="card-text">
              <h4>Joshua Hong</h4>
              <h6>Kepala Perpustakaan</h6>
            </div>
          </a>
          <a
            href="/CODINGAN/3-landingpageuser/profil/petugas/pustakawan/pustakawan.html#mingyu"
            class="card">
            <img src="mingyu.jpeg" alt="Foto Mingyu" />
            <div class="card-text">
              <h4>Mingyu Kim</h4>
              <h6>Layanan Teknis</h6>
            </div>
          </a>
        </div>
      </section>
      <section class="pengelola-situs">
        <h2>PENGELOLA SITUS</h2>
        <div class="pengelola-container">
          <div class="kartu">
            <img src="aku.jpg" alt="Fotoku" />
            <div class="text-kartu">
              <h4>Syarivatun Nisa’I Nur Aulia</h4>
              <p>Siswi SMK Telkom Sidoarjo</p>
              <p>Jurusan Sistem Informasi, Jaringan, dan Aplikasi</p>
            </div>
          </div>
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