<?php
include 'CODINGAN/assets/formkoneksi.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?error=haruslogindulu.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kontak</title>
    <link rel="stylesheet" href="kontak.css" />
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
                <li><a href="/CODINGAN/3-landingpageuser/profil/umum/profil.php">Profil</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/layanan/layanan.php">Layanan</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.php">Kontak</a></li>
        <li class="profil">
          <a href="#" class="akun"><i class="fas fa-user"></i></a>
        </li>
        <li>
          <button class="btn-logout">
            <i class="fas fa-arrow-left"></i>
            <a href="/CODINGAN/3-landingpageuser/beranda/beranda.html"
              >Kembali</a
            >
          </button>
        </li>
      </ul>
    </nav>
  </header>
  <b <div class="contact-container">
    <div class="contact-card">
        <h2>KONTAK</h2>
        <p><i class="fas fa-building"></i> <b>Library of Rivenhill Senior High School</b></p>
        <p>Sidoarjo, Jawa Timur - Indonesia</p>
        <p><i class="fab fa-whatsapp"></i> WhatsApp: +62 859-3616-4597</p>
        <p><i class="fas fa-envelope"></i> Email: syarivatunnisai@gmail.com</p>
        <p><i class="fab fa-instagram"></i> Instagram: @jeonwpnwoo</p>
        <p><i class="fab fa-linkedin"></i> LinkedIn: Syarivatun Nisa’i Nur Aulia</p>
    </div>
</div>
    <div class="container">
      <div class="kotak-form">
        <iframe
          src="https://docs.google.com/forms/d/e/1FAIpQLScH-qqiM_2Fo1tewWM7o3BbDnifO_YjoGrag_PwH3PxbrX6MQ/viewform?embedded=true"
          width="100%"
          height="500px"
          frameborder="0"
          >Loading…</iframe
        >
      </div>
      <div class="kotak-info">
        <h2>KOTAK SARAN</h2>
        <p>
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit
          omnis molestias officiis...
        </p>
      </div>
    </div>

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