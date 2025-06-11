<?php
session_start();
require_once 'formkoneksi.php';

// Cek login
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header("Location: /CODINGAN/2-loginregis/formloginusr.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'semua'; // Ambil filter dari URL

$notif = '';
$status = $_GET['status'] ?? '';

// Handle notifikasi
if ($status === 'success') {
    $notif = '<p style="color: green; text-align: center;">Item berhasil ditambahkan ke favorit!</p>';
} elseif ($status === 'exists') {
    $notif = '<p style="color: red; text-align: center;">Item sudah ada di daftar favorit.</p>';
} elseif ($status === 'removed') {
    $notif = '<p style="color: blue; text-align: center;">Item berhasil dihapus dari favorit.</p>';
}

try {
    // [1] Ambil data buku favorit
    $daftar_buku = [];
    if ($filter === 'semua' || $filter === 'buku') {
        $stmt_buku = $conn->prepare("
            SELECT 
                b.id AS buku_id,
                b.judul AS judul_buku,
                b.penulis AS penulis_buku,
                b.tahun_terbit,
                b.gambar AS gambar_buku
            FROM favorit f
            JOIN buku b ON f.buku_id = b.id
            WHERE f.user_id = ?
        ");
        $stmt_buku->execute([$user_id]);
        $daftar_buku = $stmt_buku->fetchAll(PDO::FETCH_ASSOC);
    }

    // [2] Ambil data dokumen favorit
    $daftar_dokumen = [];
    if ($filter === 'semua' || $filter === 'dokumen') {
        $stmt_dokumen = $conn->prepare("
            SELECT 
                d.id AS dokumen_id,
                d.judul AS judul_dokumen,
                d.penulis AS penulis_dokumen,
                d.tahun_terbit,
                d.tipe_dokumen
            FROM favorit f
            JOIN dokumen d ON f.dokumen_id = d.id
            WHERE f.user_id = ?
        ");
        $stmt_dokumen->execute([$user_id]);
        $daftar_dokumen = $stmt_dokumen->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Terjadi kesalahan saat mengambil data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Favorit</title>
    <link rel="stylesheet" href="favorit.css">
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css "/>
</head>
<body>

<header>
    <div class="logo">
      <img src="../../logo.png" alt="Logo Perpus" srcset="" />
    </div>
    <nav class="navbar">
            <ul>
                <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
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

<main>
    <section class="hero">
        <h1>Panel Favorit</h1>
        <p>Di sini Anda dapat melihat daftar buku dan dokumen yang telah Anda tandai sebagai favorit.</p>
    </section>

    <!-- Notifikasi -->
    <?= $notif ?>

    <!-- Tombol Filter -->
    <div class="filter-container">
        <div class="filter-buttons">
            <button onclick="location.href='?filter=semua<?= $status ? '&status='.$status : '' ?>'">Semua</button>
            <button onclick="location.href='?filter=buku<?= $status ? '&status='.$status : '' ?>'">Buku</button>
            <button onclick="location.href='?filter=dokumen<?= $status ? '&status='.$status : '' ?>'">Dokumen</button>
        </div>
    </div>

    <!-- Daftar Item Favorit -->
    <div class="documents">

        <?php if ($filter === 'semua' || $filter === 'buku'): ?>
            <?php foreach ($daftar_buku as $item): ?>
                <div class="document-card">
                    <div class="document-icon">
                        <img src="/CODINGAN/4-landingpageadmin/uploads/<?= htmlspecialchars($item['gambar_buku']) ?>" alt="<?= htmlspecialchars($item['judul_buku']) ?>" width="100">
                    </div>
                    <h3><?= htmlspecialchars($item['judul_buku']) ?></h3>
                    <div class="authors"><?= htmlspecialchars($item['penulis_buku']) ?></div>
                    <div class="meta-info">
                        <span><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($item['tahun_terbit']) ?></span>
                    </div>
                    <div class="button-container">
                        <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/detail_buku.php?id=<?= $item['buku_id'] ?>" class="read-btn">
                            Detail Buku <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($filter === 'semua' || $filter === 'dokumen'): ?>
            <?php foreach ($daftar_dokumen as $item): ?>
                <div class="document-card">
                    <div class="document-icon">
                        <i class="fas <?= getIconClass($item['tipe_dokumen']) ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($item['judul_dokumen']) ?></h3>
                    <div class="authors"><?= htmlspecialchars($item['penulis_dokumen']) ?></div>
                    <div class="meta-info">
                        <span><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($item['tahun_terbit']) ?></span>
                    </div>
                    <div class="button-container">
                        <a href="/CODINGAN/3-landingpageuser/layanan/referensi/detail/detail_dokumen.php?id=<?= $item['dokumen_id'] ?>" class="read-btn">
                            Detail Dokumen <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (empty($daftar_buku) && empty($daftar_dokumen)): ?>
            <p style="text-align: center; width: 100%;">Anda belum menambahkan item ke daftar favorit.</p>
        <?php endif; ?>
    </div>
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

<?php
function getIconClass($tipe_dokumen) {
    switch ($tipe_dokumen) {
        case 'artikel_konferensi':
            return 'fa-file-alt';
        case 'jurnal':
            return 'fa-book';
        case 'modul_pelajaran':
            return 'fa-chalkboard-teacher';
        case 'laporan':
            return 'fa-scroll';
        case 'skripsi':
            return 'fa-graduation-cap';
        default:
            return 'fa-file';
    }
}
?>

</body>
</html>