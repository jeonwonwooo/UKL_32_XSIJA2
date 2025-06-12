<?php
session_start();

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: /CODINGAN/2-loginregis/formloginadm.php");
    exit;
}

// Koneksi database
$conn = new mysqli("localhost", "root", "", "perpus");

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: {$conn->connect_error}");
}

// Ambil data statistik
$totalAdmin = $conn->query("SELECT COUNT(*) FROM admin")->fetch_row()[0];
$totalAnggota = $conn->query("SELECT COUNT(*) FROM anggota")->fetch_row()[0];
$totalBuku = $conn->query("SELECT COUNT(*) FROM buku")->fetch_row()[0];
$totalDokumen = $conn->query("SELECT COUNT(*) FROM dokumen")->fetch_row()[0];
$totalPeminjaman = $conn->query("SELECT COUNT(*) FROM peminjaman")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="icon" href="/CODINGAN/assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">   
</head>
<body>

<aside class="sidebar">
    <div class="logo">
        <img src="/CODINGAN/assets/logo.png" alt="Logo Sekolah">
    </div>
    <nav>
        <ul>
            <li><a href="/CODINGAN/4-landingpageadmin/landingpage/dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/landingpage/accadmin.php"><i class="fas fa-user"></i>Profil</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data anggota/data-anggota_list.php"><i class="fas fa-users"></i> Daftar Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data admin/data-admin_list.php"><i class="fas fa-user-shield"></i> Daftar Admin</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/artikel/artikel_list.php"><i class="fas fa-newspaper"></i> Daftar Artikel</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/buku/buku_list.php"><i class="fas fa-book"></i> Daftar Buku</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/peminjaman/peminjaman_list.php"><i class="fas fa-box-open"></i> Daftar Peminjaman</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/denda/denda_list.php"><i class="fas fa-money-bill-wave"></i> Denda Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/dokumen/dokumen_list.php"><i class="fas fa-file-alt"></i> Daftar Dokumen</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/favorit/favorit_list.php"><i class="fas fa-heart"></i> Favorit Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/rating-ulasan/rating-ulasan_list.php"><i class="fas fa-star"></i> Penilaian Pengguna</a></li>
            <li><a href="/CODINGAN/z-yakinlogout/formyakin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</aside>

<main class="content">
    <?php if (isset($_GET['status'])): ?>
        <p style="color: blue; text-align: center;">
            <?= $_GET['status'] === 'balik_admin' ? 'Terima kasih telah kembali, Admin.' :
                ($_GET['status'] === 'balik_anggota' ? 'Terima kasih telah kembali.' :
                ($_GET['status'] === 'ga_kenal' ? '<span style="color: red;">Akun tidak dikenali!</span>' : '')) ?>
        </p>
    <?php endif; ?>

    <header class="topbar">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h1>
    </header>

    <section class="dashboard-content">
        <h2>📊 Dashboard Utama</h2>
        <div class="stat-boxes">
            <div class="stat-card">
                <div class="stat-icon fas fa-users"></div>
                <h3><?= $totalAnggota ?></h3>
                <p>Total Pengguna</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon fas fa-user-shield"></div>
                <h3><?= $totalAdmin ?></h3>
                <p>Total Admin</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon fas fa-book"></div>
                <h3><?= $totalBuku ?></h3>
                <p>Total Buku</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon fas fa-file-alt"></div>
                <h3><?= $totalDokumen ?></h3>
                <p>Total Dokumen</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon fas fa-box-open"></div>
                <h3><?= $totalPeminjaman ?></h3>
                <p>Total Peminjaman</p>
            </div>
        </div>

        <div class="quick-link">
            <a href="https://docs.google.com/forms/d/1UukF8hMDByICqzV-IbEtry1HQt4NeZ9jBpYsqrJYdu0/edit"  target="_blank">💬 Lihat Kotak Keluhan</a>
        </div>
        <div class="quick-link">
            <a href="https://docs.google.com/forms/d/1jDVw_zkQB3E3MsZmYMt6i7anROh-0j5uUIi9V30Wr0s/edit"  target="_blank">💬 Lihat Kotak Saran</a>
        </div>
    </section>
</main>

</body>
</html>