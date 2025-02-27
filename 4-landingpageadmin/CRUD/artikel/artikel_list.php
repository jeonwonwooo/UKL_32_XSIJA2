<?php
include 'formkoneksi.php';

$filter = $_GET['filter'] ?? 'semua';
$order_by = $_GET['order_by'] ?? 'created_at';

$query = "
    SELECT artikel.id, artikel.judul, artikel.gambar, artikel.tanggal_publikasi, admin.nama AS nama_admin, artikel.status
    FROM artikel
    JOIN admin ON artikel.admin_id = admin.id
";

if ($filter === 'draft') {
    $query .= " WHERE artikel.status = 'draft'";
} elseif ($filter === 'published') {
    $query .= " WHERE artikel.status = 'published'";
}

if ($order_by === 'judul') {
    $query .= " ORDER BY artikel.judul ASC";
} elseif ($order_by === 'id') {
    $query .= " ORDER BY artikel.id ASC";
} elseif ($order_by === 'terbaru') {
    $query .= " ORDER BY artikel.created_at DESC";
} else {
    $query .= " ORDER BY artikel.created_at DESC";
}

$stmt = $conn->prepare($query);
$stmt->execute();
$artikel = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Artikel</title>
    <link rel="stylesheet" href="artikel_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1>Daftar Artikel</h1>
        <div class="filter-container">
            <div class="filter-dropdown">
                <button class="filter-button">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <div class="dropdown-content">
                    <div class="dropdown-section">
                        <span class="dropdown-title">Status:</span>
                        <a href="?filter=semua">Semua</a>
                        <a href="?filter=draft">Draft</a>
                        <a href="?filter=published">Published</a>
                    </div>
                    <div class="dropdown-section">
                        <span class="dropdown-title">Urutkan:</span>
                        <a href="?order_by=id">ID</a>
                        <a href="?order_by=judul">Abjad</a>
                        <a href="?order_by=terbaru">Terbaru</a>
                    </div>
                </div>
            </div>

            <a href="artikel_create.php" class="btn btn-info"><i class="fas fa-plus"></i> Tambah Artikel</a>
        </div>
        <table class="custom-table">
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
                            <a href="artikel_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                            <?php if ($row['status'] === 'draft'): ?>
                                <a href="process_artikel.php?action=publish&id=<?= $row['id'] ?>" class="btn btn-success btn-sm"><i class="fas fa-upload"></i> Publikasikan</a>
                            <?php endif; ?>
                            <a href="process_artikel.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')"><i class="fas fa-trash"></i> Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>