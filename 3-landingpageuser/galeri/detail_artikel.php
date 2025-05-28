<?php
include 'formkoneksi.php';

$id = $_GET['id'] ?? '';

$stmt = $conn->prepare("SELECT * FROM artikel WHERE id = ? AND status = 'published'");
$stmt->execute([$id]);
$artikel = $stmt->fetch();

if (!$artikel) {
    die("Artikel tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artikel['judul']) ?></title>
    <link rel="stylesheet" href="detail_artikel.css">
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
            <i class="fas fa-arrow-left"></i> <a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Kembali</a>
          </button>
        </li>
      </ul>
    </nav>
</header>

<main>
    <section class="detail-artikel">
        <div class="container mt-5">
            <h1 class="judul"><?= htmlspecialchars($artikel['judul']) ?></h1>
            <p class="tanggal-publikasi"><strong>Tanggal Publikasi:</strong> <?= htmlspecialchars($artikel['tanggal_publikasi']) ?></p>
            <img src="/CODINGAN/4-landingpageadmin/uploads/<?= htmlspecialchars($artikel['gambar']) ?>" class="img-fluid mb-4" alt="<?= htmlspecialchars($artikel['judul']) ?>">
            <div class="konten">
                <?= nl2br(htmlspecialchars($artikel['konten'])) ?>
            </div>
            <a href="galeri.php" class="btn btn-secondary">Kembali ke Galeri</a>
        </div>
    </section>
</main>
</body>

</html>