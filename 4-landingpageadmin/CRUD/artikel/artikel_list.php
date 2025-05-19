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
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                    <li><a href="/CODINGAN/z-yakinlogout/formyakin.php">Logout</a></li>
            </ul>
        </nav>
    </aside>
    <div class="container">
        <h1><i class="fas fa-newspaper"></i> Daftar Artikel</h1>
        <div class="filter-bar">
    <div class="filter-group">
        <label for="status-filter">Status:</label>
        <select id="status-filter" name="status-filter" onchange="window.location.href=this.value;">
            <option value="?filter=semua<?= isset($_GET['order_by']) ? '&order_by=' . $_GET['order_by'] : '' ?>" <?= $filter == 'semua' ? 'selected' : '' ?>>Semua</option>
            <option value="?filter=draft<?= isset($_GET['order_by']) ? '&order_by=' . $_GET['order_by'] : '' ?>" <?= $filter == 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="?filter=published<?= isset($_GET['order_by']) ? '&order_by=' . $_GET['order_by'] : '' ?>" <?= $filter == 'published' ? 'selected' : '' ?>>Published</option>
        </select>
    </div>
    <div class="filter-group">
        <label for="sort-by">Urutkan:</label>
        <select id="sort-by" name="sort-by" onchange="window.location.href=this.value;">
            <option value="?<?= $filter ? 'filter=' . $filter : 'filter=semua' ?>&order_by=id" <?= $order_by == 'id' ? 'selected' : '' ?>>ID</option>
            <option value="?<?= $filter ? 'filter=' . $filter : 'filter=semua' ?>&order_by=judul" <?= $order_by == 'judul' ? 'selected' : '' ?>>Abjad</option>
            <option value="?<?= $filter ? 'filter=' . $filter : 'filter=semua' ?>&order_by=terbaru" <?= $order_by == 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
        </select>
    </div>
    <a href="artikel_create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Artikel</a>
</div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-book"></i> Judul</th>
                    <th><i class="fas fa-image"></i> Gambar</th>
                    <th><i class="fas fa-calendar-alt"></i> Tanggal Publikasi</th>
                    <th><i class="fas fa-user"></i> Admin (Uploader)</th>
                    <th><i class="fas fa-check-circle"></i> Status</th>
                    <th><i class="fas fa-cogs"></i> Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($artikel as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td>
                            <?php if ($row['gambar']): ?>
                                <img src="../../uploads/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>" width="50">
                            <?php else: ?>
                                Tidak Ada Gambar
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['tanggal_publikasi']) ?></td>
                        <td><?= htmlspecialchars($row['nama_admin']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td class="aksi-td">
                            <div class="aksi-container">
                            <a href="artikel_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php if ($row['status'] === 'draft'): ?>
                                <a href="process_artikel.php?action=publish&id=<?= $row['id'] ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-upload"></i> Publikasikan
                                </a>
                            <?php endif; ?>
                            <a href="process_artikel.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>