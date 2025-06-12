<?php
include 'formkoneksi.php';

// Ambil parameter filter dan pencarian dari URL
$filter = $_GET['filter'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Query dasar - join ke tabel kategori
$query = "
    SELECT 
        dokumen.id, 
        dokumen.judul, 
        dokumen.penulis, 
        dokumen.tahun_terbit, 
        dokumen.tipe_dokumen, 
        dokumen.deskripsi, 
        dokumen.file_path, 
        dokumen.status
    FROM dokumen
    WHERE 1=1
";

$conditions = [];
$params = [];

// Filter status
if ($filter === 'tersedia') {
    $conditions[] = "dokumen.status = ?";
    $params[] = 'tersedia';
} elseif ($filter === 'tidak tersedia') {
    $conditions[] = "dokumen.status = ?";
    $params[] = 'tidak tersedia';
}

// Pencarian
if (!empty($search)) {
    $conditions[] = "(dokumen.judul LIKE ? OR dokumen.penulis LIKE ? OR dokumen.tipe_dokumen LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Gabungkan kondisi
if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

// Eksekusi query
try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $dokumen = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="dokumen_list.css">
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
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/buku/buku_list.php"><i class="fas fa-book"></i> Daftar Buku</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/peminjaman/peminjaman_list.php"><i class="fas fa-box-open"></i> Daftar Peminjaman</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/denda/denda_list.php"><i class="fas fa-money-bill-wave"></i> Denda Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/dokumen/dokumen_list.php" class="active"><i class="fas fa-file-alt"></i> Daftar Dokumen</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/favorit/favorit_list.php"><i class="fas fa-heart"></i> Favorit Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/rating-ulasan/rating-ulasan_list.php"><i class="fas fa-star"></i> Penilaian Pengguna</a></li>
            <li><a href="/CODINGAN/z-yakinlogout/formyakin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</aside>

<div class="container">
    <h1>Daftar Dokumen</h1>

    <!-- Filter and Search Bar -->
    <div class="filter-bar">
        <a href="dokumen_create.php" class="btn btn-success">Tambah Dokumen</a>
        <form method="GET" class="filter-form">
            <select name="filter" onchange="this.form.submit()">
                <option value="semua" <?= ($filter === 'semua') ? 'selected' : '' ?>>Semua</option>
                <option value="tersedia" <?= ($filter === 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                <option value="tidak tersedia" <?= ($filter === 'tidak tersedia') ? 'selected' : '' ?>>Tidak Tersedia</option>
            </select>
            <input type="text" name="search" placeholder="Cari judul, penulis, atau tipe dokumen..." value="<?= htmlspecialchars($search) ?>">
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
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Tipe Dokumen</th>
                <th>File Dokumen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($dokumen)): ?>
                <tr>
                    <td colspan="9" style="text-align:center;">Tidak ada data ditemukan</td>
                </tr>
            <?php else: ?>
                <?php foreach ($dokumen as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['penulis']) ?></td>
                        <td><?= htmlspecialchars($row['tahun_terbit']) ?></td>
                        <td><?= htmlspecialchars($row['deskripsi'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['tipe_dokumen'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <?php if (!empty($row['file_path']) && file_exists("../../" . $row['file_path'])): ?>
                                <a href="../../<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-primary btn-sm">Download</a>
                            <?php else: ?>
                                File tidak tersedia
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="dokumen_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="process_dokumen.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>