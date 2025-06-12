<?php
include 'formkoneksi.php';

// Ambil parameter filter dan pencarian dari URL
$filter_status = $_GET['filter_status'] ?? 'semua';
$filter_tipe = $_GET['filter_tipe'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Query dasar
$query = "SELECT * FROM buku";

$conditions = [];
$params = [];

// Tambahkan kondisi filter status
if ($filter_status === 'tersedia' || $filter_status === 'dipinjam' || $filter_status === 'habis') {
    $conditions[] = "status = ?";
    $params[] = $filter_status;
}

// Tambahkan kondisi filter tipe buku
if ($filter_tipe === 'buku fisik' || $filter_tipe === 'buku elektronik') {
    $conditions[] = "tipe_buku = ?";
    $params[] = $filter_tipe;
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="buku_list.css">
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
            <li><a href="/CODINGAN/4-landingpageadmin/landingpage/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/landingpage/accadmin.php"><i class="fas fa-user"></i>Profil</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data anggota/data-anggota_list.php"><i class="fas fa-users"></i> Daftar Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/data admin/data-admin_list.php"><i class="fas fa-user-shield"></i> Daftar Admin</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/artikel/artikel_list.php"><i class="fas fa-newspaper"></i> Daftar Artikel</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/buku/buku_list.php" class="active"><i class="fas fa-book"></i> Daftar Buku</a></li>
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
    <h1>Daftar Buku</h1>

    <!-- Filter and Search Bar -->
    <div class="filter-bar">
        <a href="buku_create.php" class="btn btn-success">Tambah Buku</a>
        <form method="GET" class="filter-form">
            <!-- Filter Status -->
            <select name="filter_status" onchange="this.form.submit()">
                <option value="semua" <?= ($filter_status === 'semua') ? 'selected' : '' ?>>Semua Status</option>
                <option value="tersedia" <?= ($filter_status === 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                <option value="dipinjam" <?= ($filter_status === 'dipinjam') ? 'selected' : '' ?>>Dipinjam</option>
                <option value="habis" <?= ($filter_status === 'habis') ? 'selected' : '' ?>>Habis</option>
            </select>

            <!-- Filter Tipe Buku -->
            <select name="filter_tipe" onchange="this.form.submit()">
                <option value="semua" <?= ($filter_tipe === 'semua') ? 'selected' : '' ?>>Semua Tipe</option>
                <option value="buku fisik" <?= ($filter_tipe === 'buku fisik') ? 'selected' : '' ?>>Buku Fisik</option>
                <option value="buku elektronik" <?= ($filter_tipe === 'buku elektronik') ? 'selected' : '' ?>>Buku Elektronik</option>
            </select>

            <!-- Pencarian -->
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
                            <td><a href="<?= $file_url ?>" target="_blank" class="btn btn-primary btn-sm">Buka Ebook</a></td>
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