<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: /CODINGAN/2-loginregis/formloginadm.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <h2>Admin Panel</h2>
        </div>
        <nav>
            <ul>
                <li><a href="/CODINGAN/4-landingpageadmin/landingpage/dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data anggota/data-anggota_list.php">Daftar Pengguna</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data admin/data-admin_list.php">Daftar Admin</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/artikel/artikel_list.php">Daftar Artikel</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/buku/buku_list.php">Daftar Buku</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/peminjaman/peminjaman_list.php">Daftar Peminjaman</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/dokumen/dokumen_list.php">Daftar Dokumen</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/favorit/favorit_list.php">Favorit Pengguna</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/rating-ulasan/rating-ulasan_list.php">Penilaian Pengguna</a></li>
                    <li><a href="/CODINGAN/z-yakinlogout/formyakin.php">Logout</a></li>
            </ul>
        </nav>
    </aside>
    <main class="content">
        <?php if (isset($_GET['status'])) { ?>
            <?php if ($_GET['status'] === 'balik_admin') { ?>
                <p style="color: blue; text-align: center;">Terima kasih telah kembali, Admin.</p>
            <?php } elseif ($_GET['status'] === 'balik_anggota') { ?>
                <p style="color: blue; text-align: center;">Terima kasih telah kembali.</p>
            <?php } elseif ($_GET['status'] === 'ga_kenal') { ?>
                <p style="color: red; text-align: center;">Akun tidak dikenali!</p>
            <?php } ?>
        <?php } ?>
        <header class="topbar">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
        </header>
        <section class="dashboard-content">
            <h2>Dashboard</h2>
            <p>This is your admin dashboard. You can manage users, articles, books, and loans from here.</p>
        </section>
    </main>
</body>
</html>