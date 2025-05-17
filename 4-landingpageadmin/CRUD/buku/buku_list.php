<?php
include 'formkoneksi.php';

// Ambil parameter filter dan pencarian dari URL
$filter = $_GET['filter'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Query dasar
$query = "SELECT * FROM buku";

$conditions = [];
$params = [];

// Tambahkan kondisi filter
if ($filter === 'tersedia' || $filter === 'dipinjam' || $filter === 'habis') {
    $conditions[] = "status = ?";
    $params[] = $filter;
}

// Tambahkan pencarian
if (!empty($search)) {
    $conditions[] = "(judul LIKE ? OR penulis LIKE ? OR isbn LIKE ?)";
    $params[] = "%$search%";
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
                    <li><a href="/CODINGAN/z-yakinlogout/formyakinadm.html">Logout</a></li>
        </ul>
    </nav>
</aside>

<div class="container">
    <h1>Daftar Buku</h1>

    <!-- Filter and Search Bar -->
    <div class="filter-bar">
        <a href="buku_create.php" class="btn btn-success">Tambah Buku</a>
        <form method="GET" class="filter-form">
            <select name="filter" onchange="this.form.submit()">
                <option value="semua" <?= ($filter === 'semua') ? 'selected' : '' ?>>Semua</option>
                <option value="tersedia" <?= ($filter === 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                <option value="dipinjam" <?= ($filter === 'dipinjam') ? 'selected' : '' ?>>Dipinjam</option>
                <option value="habis" <?= ($filter === 'habis') ? 'selected' : '' ?>>Habis</option>
            </select>
            <input type="text" name="search" placeholder="Cari judul, penulis, atau ISBN..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>

    <!-- Table -->
    <table class="custom-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Tahun Terbit</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Tipe Buku</th>
            <th>Stok</th>
            <th>File Ebook</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($buku) > 0): ?>
            <?php foreach ($buku as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['judul']) ?></td>
                    <td><?= htmlspecialchars($row['penulis']) ?></td>
                    <td><?= htmlspecialchars($row['tahun_terbit']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($row['kategori'])) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['tipe_buku']) ?></td>
                    <td><?= htmlspecialchars($row['stok']) ?></td>

                    <!-- Kolom File Ebook -->
                    <?php 
$file_url = "/CODINGAN/4-landingpageadmin/" . htmlspecialchars($row['file_path']);
if (!empty($row['file_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $file_url)) { ?>
    <td><a href="<?= $file_url ?>" target="_blank">Buka Ebook</a></td>
<?php } else { ?>
    <td>-</td>
<?php } ?>

                    <!-- Tombol Aksi -->
                    <td>
                        <a href="buku_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="process_buku.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" style="text-align:center;">Tidak ada data ditemukan</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
</body>
</html>