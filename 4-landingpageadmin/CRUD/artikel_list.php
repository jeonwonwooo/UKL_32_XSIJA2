<?php
include '../../formkoneksi.php';

// Ambil filter dari URL
$filter = $_GET['filter'] ?? 'semua';

// Query berdasarkan filter
if ($filter === 'draft') {
    $stmt = $conn->prepare("
        SELECT artikel.id, artikel.judul, artikel.gambar, artikel.tanggal_publikasi, admin.nama AS nama_admin, artikel.status
        FROM artikel
        JOIN admin ON artikel.admin_id = admin.id
        WHERE artikel.status = 'draft'
        ORDER BY artikel.created_at DESC
    ");
} elseif ($filter === 'published') {
    $stmt = $conn->prepare("
        SELECT artikel.id, artikel.judul, artikel.gambar, artikel.tanggal_publikasi, admin.nama AS nama_admin, artikel.status
        FROM artikel
        JOIN admin ON artikel.admin_id = admin.id
        WHERE artikel.status = 'published'
        ORDER BY artikel.created_at DESC
    ");
} else {
    $stmt = $conn->prepare("
        SELECT artikel.id, artikel.judul, artikel.gambar, artikel.tanggal_publikasi, admin.nama AS nama_admin, artikel.status
        FROM artikel
        JOIN admin ON artikel.admin_id = admin.id
        ORDER BY artikel.created_at DESC
    ");
}

$stmt->execute();
$artikel = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Artikel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Daftar Artikel</h1>
        <div class="mb-3">
            <a href="?filter=semua" class="btn btn-primary">Semua</a>
            <a href="?filter=draft" class="btn btn-warning">Draft</a>
            <a href="?filter=published" class="btn btn-success">Published</a>
            <a href="artikel_create.php" class="btn btn-info">Tambah Artikel</a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Gambar</th>
                    <th>Tanggal Publikasi</th>
                    <th>Admin (Uploader)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($artikel as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['judul']) ?></td>
                    <td><img src="../../uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>" width="100"></td>
                    <td><?= htmlspecialchars($row['tanggal_publikasi']) ?></td>
                    <td><?= htmlspecialchars($row['nama_admin']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <a href="artikel_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <?php if ($row['status'] === 'draft'): ?>
                            <a href="process_artikel.php?action=publish&id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Publikasikan</a>
                        <?php endif; ?>
                        <a href="process_artikel.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>