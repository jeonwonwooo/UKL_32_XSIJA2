<?php
include 'formkoneksi.php'; // Pastikan ada koneksi database

// Cek apakah buku_id tersedia di URL
if (!isset($_GET['id'])) {
    echo "ID buku tidak ditemukan!";
    exit;
}

$buku_id = $_GET['id'];

// Ambil data buku dari database
$query = "SELECT * FROM buku WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

// Pastikan buku ditemukan
if (!$buku) {
    echo "Buku tidak ditemukan!";
    exit;
}

// Ambil data buku dari database dengan join kategori
$query = "SELECT pb.*, pk.nama_kategori FROM buku pb
          LEFT JOIN kategori pk ON pb.kategori_id = pk.id
          WHERE pb.id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil rating rata-rata dan jumlah ulasan
$query = "SELECT COALESCE(AVG(nilai), 0) as rata_rating, COUNT(*) as jumlah_ulasan FROM rating WHERE buku_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$rating_result = $stmt->fetch(PDO::FETCH_ASSOC);
$rata_rating = round($rating_result['rata_rating'], 1);
$jumlah_ulasan = $rating_result['jumlah_ulasan'];

// Cek apakah buku sudah ada di favorit pengguna (tanpa login)
$query = "SELECT * FROM favorit WHERE buku_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$buku_id]);
$favorit_aktif = $stmt->rowCount() > 0;

// Pastikan gambar buku ada atau gunakan default
$folder_uploads = '/CODINGAN/4-landingpageadmin/uploads/'; // Path utama penyimpanan gambar

// Path gambar dari database
$gambar_path = $folder_uploads . htmlspecialchars($buku['gambar']);

// Path gambar default
$default_gambar = $folder_uploads . 'default.jpg';

// Cek apakah file gambar benar-benar ada di folder
if (!empty($buku['gambar']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $gambar_path)) {
    $gambar = $gambar_path;
} else {
    $gambar = $default_gambar;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku - <?php echo htmlspecialchars($buku['judul']); ?></title>
    <link rel="stylesheet" href="detail_buku.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script>
        function tampilkanNotifikasi() {
            document.getElementById("notif-favorit").style.display = "block";
            setTimeout(() => {
                document.getElementById("notif-favorit").style.display = "none";
            }, 3000);
        }
    </script>
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
        <section class="judul">
            <h1>Detail Buku</h1>
            <h2><?php echo htmlspecialchars($buku['judul']); ?></h2>
        </section>
        <section class="detail-buku">
            <img class="gambar-buku" src="<?php echo $gambar; ?>" alt="<?php echo htmlspecialchars($buku['judul']); ?>" width="200">
            <div class="informasi-buku">
                <p><strong>Penulis:</strong> <?php echo htmlspecialchars($buku['penulis']); ?></p>
                <p><strong>ISBN:</strong> <?php echo isset($buku['isbn']) ? htmlspecialchars($buku['isbn']) : '-'; ?></p>
                <p><strong>Tahun Terbit:</strong> <?php echo htmlspecialchars($buku['tahun_terbit']); ?></p>
                <p><strong>Jumlah Halaman:</strong> <?php echo htmlspecialchars($buku['jumlah_halaman']); ?></p>
                <p><strong>Kategori:</strong> <?php echo isset($buku['kategori_id']) ? htmlspecialchars($buku['nama_kategori']) : '-'; ?></p>
                <p><strong>Tipe Buku:</strong> <?php echo htmlspecialchars($buku['tipe_buku']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($buku['status']); ?></p>
                <p><strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($buku['deskripsi'])); ?></p>
            </div>
        </section>
        
<!-- Action Buttons -->
<div class="action-buttons">
    <form action="proses_pinjam.php" method="POST" style="display: inline-block;">
        <input type="hidden" name="buku_id" value="<?php echo $buku_id; ?>">
        <button type="submit" class="btn-pinjam">Pinjam Sekarang</button>
    </form>

    <form action="proses_favorit.php" method="POST" onsubmit="tampilkanNotifikasi()" style="display: inline-block;">
        <input type="hidden" name="buku_id" value="<?php echo $buku_id; ?>">
        <button type="submit" class="btn-favorit" <?php echo $favorit_aktif ? 'disabled' : ''; ?>>
            <?php echo $favorit_aktif ? 'Sudah di Favorit' : 'Tambah ke Favorit'; ?>
        </button>
    </form>
</div>

<!-- Notifikasi Favorit -->
<p id="notif-favorit" style="display: none; color: green;">Buku berhasil ditambahkan ke favorit Anda!</p>
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