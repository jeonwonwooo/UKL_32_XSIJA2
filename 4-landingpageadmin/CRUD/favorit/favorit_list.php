<?php
include 'formkoneksi.php';

// Fetch all favorite entries
$stmt = $conn->query("SELECT f.id, f.user_id, f.buku_id, f.created_at, a.username, b.judul 
                     FROM favorit f 
                     JOIN anggota a ON f.user_id = a.id 
                     JOIN buku b ON f.buku_id = b.id");
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Favorit</title>
    <link rel="stylesheet" href="favorit_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
    <div class="container">
        <h1><i class="fas fa-heart"></i> Daftar Favorit</h1>
        <a href="favorit_create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Favorit</a>
        <table class="custom-table">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-user"></i> Pengguna</th>
                    <th><i class="fas fa-book"></i> Buku</th>
                    <th><i class="fas fa-calendar-alt"></i> Dibuat Pada</th>
                    <th><i class="fas fa-cogs"></i> Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($favorites)): ?>
                    <?php foreach ($favorites as $favorite): ?>
                        <tr>
                            <td><?= htmlspecialchars($favorite['id']) ?></td>
                            <td><?= htmlspecialchars($favorite['username']) ?></td>
                            <td><?= htmlspecialchars($favorite['judul']) ?></td>
                            <td><?= htmlspecialchars($favorite['created_at']) ?></td>
                            <td>
                                <a href="favorit_edit.php?id=<?= $favorite['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="process_favorit.php?id=<?= $favorite['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Tidak ada data favorit.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>