<?php
include 'formkoneksi.php';

$stmt = $conn->query("SELECT * FROM anggota");
$anggotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="data-anggota_list.css">
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
    <div class="container">
        <h1><i class="fas fa-users"></i> Daftar Anggota</h1>
        <a href="data-anggota_create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Anggota</a>
        <table class="custom-table">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-user"></i> Username</th>
                    <th><i class="fas fa-signature"></i> Nama</th>
                    <th><i class="fas fa-envelope"></i> Email</th>
                    <th><i class="fas fa-image"></i> Foto Profil</th>
                    <th><i class="fas fa-calendar-alt"></i> Dibuat Pada</th>
                    <th><i class="fas fa-cogs"></i> Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($anggotas as $anggota): ?>
                    <tr>
                        <td><?= htmlspecialchars($anggota['id']) ?></td>
                        <td><?= htmlspecialchars($anggota['username']) ?></td>
                        <td><?= htmlspecialchars($anggota['nama']) ?></td>
                        <td><?= htmlspecialchars($anggota['email']) ?></td>
                        <td>
                            <?php if ($anggota['foto_profil']): ?>
                                <img src="../../uploads/<?= htmlspecialchars($anggota['foto_profil']) ?>" alt="Foto Profil" width="50">
                            <?php else: ?>
                                Tidak Ada Foto
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($anggota['created_at']) ?></td>
                        <td>
                            <a href="data-anggota_edit.php?id=<?= $anggota['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="process_data-anggota.php?action=delete&id=<?= $anggota['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>