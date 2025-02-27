<?php
include 'formkoneksi.php';

$stmt = $conn->query("SELECT * FROM admin");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Admin</title>
    <link rel="stylesheet" href="data-admin_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container">
        <h1><i class="fas fa-users-cog"></i> Daftar Admin</h1>
        <a href="data-admin_create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Admin
        </a>
        <table class="custom-table">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-user"></i> Nama</th>
                    <th><i class="fas fa-at"></i> Username</th>
                    <th><i class="fas fa-image"></i> Foto Profil</th>
                    <th><i class="fas fa-cogs"></i> Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['id']) ?></td>
                        <td><?= htmlspecialchars($admin['nama']) ?></td>
                        <td><?= htmlspecialchars($admin['username']) ?></td>
                        <td>
                            <?php if ($admin['foto_profil']): ?>
                                <img src="../uploads/<?= htmlspecialchars($admin['foto_profil']) ?>" alt="Foto Profil" width="50">
                            <?php else: ?>
                                Tidak Ada Foto
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="data-admin_edit.php?id=<?= $admin['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="process_data-admin.php?action=delete&id=<?= $admin['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
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