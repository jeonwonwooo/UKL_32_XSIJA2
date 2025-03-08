<?php include 'formkoneksi.php'; ?>

<?php
// Filter dan Pengurutan
$filter = $_GET['filter'] ?? 'semua';
$order_by = $_GET['order_by'] ?? 'created_at';

$query = "
    SELECT buku.id, buku.judul, buku.gambar, buku.tahun_terbit, kategori.nama_kategori, buku.status
    FROM buku
    JOIN kategori ON buku.kategori_id = kategori.id
";

if ($filter === 'tersedia') {
    $query .= " WHERE buku.status = 'tersedia'";
} elseif ($filter === 'dipinjam') {
    $query .= " WHERE buku.status = 'dipinjam'";
} elseif ($filter === 'habis') {
    $query .= " WHERE buku.status = 'habis'";
}

if ($order_by === 'judul') {
    $query .= " ORDER BY buku.judul ASC";
} elseif ($order_by === 'id') {
    $query .= " ORDER BY buku.id ASC";
} elseif ($order_by === 'terbaru') {
    $query .= " ORDER BY buku.created_at DESC";
} else {
    $query .= " ORDER BY buku.created_at DESC";
}

$stmt = $conn->prepare($query);
$stmt->execute();
$buku = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku</title>
    <link rel="stylesheet" href="buku_list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1>Daftar Buku</h1>
        <div class="filter-container">
            <div class="filter-dropdown">
                <button class="filter-button">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <div class="dropdown-content">
                    <div class="dropdown-section">
                        <span class="dropdown-title">Status:</span>
                        <a href="?filter=semua">Semua</a>
                        <a href="?filter=tersedia">Tersedia</a>
                        <a href="?filter=dipinjam">Dipinjam</a>
                        <a href="?filter=habis">Habis</a>
                    </div>
                    <div class="dropdown-section">
                        <span class="dropdown-title">Urutkan:</span>
                        <a href="?order_by=id">ID</a>
                        <a href="?order_by=judul">Abjad</a>
                        <a href="?order_by=terbaru">Terbaru</a>
                    </div>
                </div>
            </div>

            <a href="buku_create.php" class="btn btn-info"><i class="fas fa-plus"></i> Tambah Buku</a>
        </div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Gambar</th>
                    <th>Tahun Terbit</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($buku as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><img src="../../uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>" width="100"></td>
                        <td><?= htmlspecialchars($row['tahun_terbit']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <a href="buku_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                            <a href="process_buku.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')"><i class="fas fa-trash"></i> Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>