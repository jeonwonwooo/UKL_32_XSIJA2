<?php
include 'formkoneksi.php';

$stmt = $conn->query("SELECT * FROM anggota");
$anggotas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota</title>
    <link rel="stylesheet" href="data-anggota_list.css">
</head>

<body>
<aside class="sidebar">
            <div class="logo">
                <h2>Admin Panel</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data anggota/data-anggota_list.php">Daftar Pengguna</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data admin/data-admin_list.php">Daftar Admin</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/artikel/artikel_list.php">Daftar Artikel</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/buku/buku_list.php">Daftar Buku</a></li>
                    <li><a href="/CODINGAN/4-landingpageadmin/CRUD/peminjaman/peminjaman_list.php">Daftar Peminjaman</a></li>
                    <li><a href="/CODINGAN/z-yakinlogout/formyakin.html">Logout</a></li>
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
                                <img src="../uploads/<?= htmlspecialchars($anggota['foto_profil']) ?>" alt="Foto Profil" width="50">
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