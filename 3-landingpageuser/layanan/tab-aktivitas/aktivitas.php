<?php
require_once 'formkoneksi.php';

try {
  $current_date = date('Y-m-d');
  $query_stats = "
        SELECT 'total_buku' as stat_name, COUNT(*) as stat_value FROM buku
        UNION ALL
        SELECT 'peminjaman_aktif', COUNT(*) FROM peminjaman WHERE status = 'dipinjam'
        UNION ALL
        SELECT 'peminjaman_hari_ini', COUNT(*) FROM peminjaman WHERE tanggal_pinjam = :current_date
        UNION ALL
        SELECT 'pengembalian_hari_ini', COUNT(*) FROM peminjaman WHERE status = 'dikembalikan' AND tanggal_kembali = :current_date2
        UNION ALL
        SELECT 'ebook_dipinjam', COUNT(*) FROM peminjaman WHERE tipe_buku = 'ebook' AND status = 'dipinjam'
    ";

  $stmt_stats = $conn->prepare($query_stats);
  $stmt_stats->bindParam(':current_date', $current_date);
  $stmt_stats->bindParam(':current_date2', $current_date);
  $stmt_stats->execute();

  $stats = [];
  while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['stat_name']] = $row['stat_value'];
  }

  $query_activities = "
        SELECT 
            p.id AS peminjaman_id,
            b.judul AS judul_buku,
            a.nama AS nama_anggota,
            p.tanggal_pinjam,
            p.status,
            p.tipe_buku
        FROM peminjaman p
        JOIN buku b ON p.buku_id = b.id
        JOIN anggota a ON p.anggota_id = a.id
        ORDER BY p.id DESC
        LIMIT 5
    ";
  $stmt_activities = $conn->prepare($query_activities);
  $stmt_activities->execute();
  $activities = $stmt_activities->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Katalog Buku</title>
  <link rel="stylesheet" href="aktivitas.css">
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
        <li><a href="#">Favorit</a></li>
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
      <h3>Aktivitas Anda</h3>
      <p>
        Statistik peminjaman dan pengembalian buku Anda di perpustakaan kami. Pantau aktivitas terkini dari peminjaman dan pengembalian Anda.
      </p>
    </div>
    <div class="isi">
      <!-- Statistik Utama -->
      <h3>Statistik Utama</h3>
      <div class="statistik-wrapper">
        <!-- Teks Penjelasan di KIRI -->
        <div class="text1">
          <p>Statistik ini akan diperbarui secara <i>real-time.</i> Berisikan tentang riwayat aktivitas secara keseluruhan di perpustakaan kami. Anda dapat memantau aktivitas Anda melalui ringkasa statistik ini.</p>
        </div>
        <!-- Tabel Statistik di KANAN -->
        <div class="statistik">
          <table>
            <thead>
              <tr>
                <th>No.</th>
                <th>Nama Statistik</th>
                <th>Nilai</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>Total Buku</td>
                <td><?= htmlspecialchars($stats['total_buku'] ?? 0) ?></td>
              </tr>
              <tr>
                <td>2</td>
                <td>Total Peminjaman Aktif</td>
                <td><?= htmlspecialchars($stats['peminjaman_aktif'] ?? 0) ?></td>
              </tr>
              <tr>
                <td>3</td>
                <td>Peminjaman Hari Ini</td>
                <td><?= htmlspecialchars($stats['peminjaman_hari_ini'] ?? 0) ?></td>
              </tr>
              <tr>
                <td>4</td>
                <td>Pengembalian Hari Ini</td>
                <td><?= htmlspecialchars($stats['pengembalian_hari_ini'] ?? 0) ?></td>
              </tr>
              <tr>
                <td>5</td>
                <td>Total eBook Diakses</td>
                <td><?= htmlspecialchars($stats['ebook_dipinjam'] ?? 0) ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="aktivitas">
      <h3>Aktivitas Terbaru</h3>
      <table>
        <thead>
          <tr>
            <th>No.</th>
            <th>Judul Buku</th>
            <th>Tanggal Pinjam</th>
            <th>Status</th>
            <th>Tipe Buku</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($activities)): ?>
            <?php foreach ($activities as $index => $activity): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($activity['judul_buku']) ?></td>
                <td><?= htmlspecialchars($activity['tanggal_pinjam']) ?></td>
                <td><?= htmlspecialchars(ucfirst($activity['status'])) ?></td>
                <td><?= htmlspecialchars(ucfirst($activity['tipe_buku'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align: center;">Tidak ada aktivitas terbaru.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Laporan Kerusakan -->
    <div class="laporan-kerusakan">
      <div class="left-column">
        <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSeeHmEzoQgW-ZkDoWk7NKS_2sXPFqMoPTHLIkQ-Dd5-kF7xwQ/viewform?embedded=true"
          allowfullscreen
          loading="lazy">
        </iframe>
      </div>
      <div class="right-column">
        <h3>Laporan Kerusakan</h3>
        <p>
          Jika Anda menemukan kerusakan pada buku atau fasilitas perpustakaan, silakan laporkan melalui formulir di samping.
          Kami akan memproses laporan Anda secepat mungkin untuk memastikan kenyamanan pengguna perpustakaan.
          Pastikan untuk mengisi formulir dengan informasi yang jelas dan akurat agar kami dapat menindaklanjuti laporan Anda dengan efisien.
        </p>
      </div>
    </div>
    </div>
  </main>
  <footer class="footer">
    <div class="container">
      <div class="left">
        <img
          src="logo.png"
          alt="Library of Riverhill Senior High School logo" />
        <p>
          Lorem ipsum dolor sit amet consectetur adipiscing elit. Repudiandae
          omnis molestias nobis. Lorem ipsum dolor sit amet consectetur
          adipiscing elit. Repudiandae omnis molestias nobis.
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
          <li><a href="beranda.html">Beranda</a></li>
          <li><a href="/CODINGAN//3-landingpageuser/layanan/layanan.html">Layanan</a></li>
          <li><a href="/CODINGAN//3-landingpageuser/galeri/galeri.html">Galeri</a></li>
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