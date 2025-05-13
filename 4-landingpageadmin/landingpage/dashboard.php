<?php
session_start();

// Periksa apakah admin sudah login
if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit();
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
                    <li><a href="/CODINGAN/4-landingpageadmin/landingpage/dashboard.php">Dashboard</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data anggota/data-anggota_list.php">Daftar Pengguna</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data admin/data-admin_list.php">Daftar Admin</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/artikel/artikel_list.php">Daftar Artikel</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/buku/buku_list.php">Daftar Buku</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/peminjaman/peminjaman_list.php">Daftar Peminjaman</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/dokumen/dokumen_list.php">Daftar Dokumen</a></li>
                    <li><a href="/CODINGAN/z-yakinlogout/formyakinadm.html">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="topbar">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
            </header>
            <section class="dashboard-content">
                <h2>Dashboard</h2>
                <p>This is your admin dashboard. You can manage users, articles, books, and loans from here.</p>
            </section>
        </main>
    </div>
</body>
</html>