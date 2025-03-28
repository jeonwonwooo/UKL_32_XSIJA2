<?php
include 'formkoneksi.php';

// Ambil parameter filter dan pencarian dari URL
$filter = $_GET['filter'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Query dasar
$query = "
    SELECT buku.id, buku.judul, buku.gambar, buku.tahun_terbit, kategori.nama_kategori AS kategori_nama, buku.status, buku.tipe_buku, buku.file_path
    FROM buku
    JOIN kategori ON buku.kategori_id = kategori.id
";

$conditions = [];
$params = [];

// Tambahkan kondisi filter
if ($filter === 'tersedia' || $filter === 'dipinjam' || $filter === 'habis') {
    $conditions[] = "buku.status = ?";
    $params[] = $filter;
}

// Tambahkan pencarian
if (!empty($search)) {
    $conditions[] = "(buku.judul LIKE ? OR kategori.nama_kategori LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$buku = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku</title>
    <link rel="stylesheet" href="buku_list.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Daftar Buku</h1>
        
        <!-- Tombol Tambah Buku -->
        <a href="buku_create.php" class="btn btn-success mb-3">Tambah Buku</a>

        <!-- Filter -->
        <div class="filter-container">
            <form method="GET">
                <select name="filter" onchange="this.form.submit()">
                    <option value="semua" <?= ($filter === 'semua') ? 'selected' : '' ?>>Semua</option>
                    <option value="tersedia" <?= ($filter === 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                    <option value="dipinjam" <?= ($filter === 'dipinjam') ? 'selected' : '' ?>>Dipinjam</option>
                    <option value="habis" <?= ($filter === 'habis') ? 'selected' : '' ?>>Habis</option>
                </select>
                <input type="text" name="search" placeholder="Cari judul atau kategori..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Cari</button>
            </form>
        </div>

        <!-- Tabel Daftar Buku -->
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Gambar</th>
                    <th>Tahun Terbit</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Tipe Buku</th>
                    <th>File Ebook</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($buku as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td>
                            <?php if ($row['gambar']): ?>
                                <img src="../../uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="Cover" width="100">
                            <?php else: ?>
                                Tidak ada gambar
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['tahun_terbit']) ?></td>
                        <td><?= htmlspecialchars($row['kategori_nama']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['tipe_buku'] === 'Buku Elektronik' ? 'Buku Elektronik' : 'Buku Fisik') ?></td>
                        <td>
                            <?php if ($row['tipe_buku'] === 'Buku Elektronik'): ?>
                                <?php if (!empty($row['file_path']) && file_exists("../../" . $row['file_path'])): ?>
                                    <a href="../../<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-primary btn-sm">Download</a>
                                <?php else: ?>
                                    File tidak tersedia
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="buku_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="process_buku.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>