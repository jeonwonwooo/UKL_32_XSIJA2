<?php
include 'formkoneksi.php';

// Debugging: Pastikan $conn ada
if (!$conn) {
    die("Koneksi database tidak tersedia.");
}

// Ambil parameter filter dan search dari URL
$filter = $_GET['filter'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Query dasar
$query = "
    SELECT 
        p.id, 
        a.username, 
        b.judul, 
        b.tipe_buku, 
        p.tanggal_pinjam, 
        p.batas_pengembalian, 
        p.status, 
        p.status_pengajuan,
        p.tanggal_kembali,
        d.nominal AS denda_nominal,
        d.status AS denda_status
    FROM 
        peminjaman p
    JOIN 
        anggota a ON p.anggota_id = a.id
    JOIN 
        buku b ON p.buku_id = b.id
    LEFT JOIN 
        denda d ON p.id = d.peminjaman_id AND d.status = 'belum_dibayar'
";

// Tambahkan kondisi filter
if ($filter === 'dipinjam') {
    $query .= " WHERE p.status = 'dipinjam'";
} elseif ($filter === 'dikembalikan') {
    $query .= " WHERE p.status = 'dikembalikan'";
} elseif ($filter === 'denda') {
    $query .= " WHERE p.status = 'dipinjam' AND CURDATE() > p.batas_pengembalian";
} elseif ($filter === 'pengajuan') {
    $query .= " WHERE p.status_pengajuan = 'menunggu'";
}

// Tambahkan pencarian
if (!empty($search)) {
    if (strpos($query, 'WHERE') !== false) {
        $query .= " AND (b.judul LIKE ? OR a.username LIKE ?)";
    } else {
        $query .= " WHERE (b.judul LIKE ? OR a.username LIKE ?)";
    }
}

// Persiapkan statement
$stmt = $conn->prepare($query);

// Bind parameter pencarian jika ada
if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmt->bindValue(1, $searchParam, PDO::PARAM_STR); // Binding pertama
    $stmt->bindValue(2, $searchParam, PDO::PARAM_STR); // Binding kedua
}

// Eksekusi query
try {
    $stmt->execute();
    $peminjaman = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="peminjaman_list.css">
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
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/peminjaman/peminjaman_list.php" class="active"><i class="fas fa-box-open" class="active"></i> Daftar Peminjaman</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/denda/denda_list.php"><i class="fas fa-money-bill-wave"></i> Denda Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/dokumen/dokumen_list.php"><i class="fas fa-file-alt"></i> Daftar Dokumen</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/favorit/favorit_list.php"><i class="fas fa-heart"></i> Favorit Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/rating-ulasan/rating-ulasan_list.php"><i class="fas fa-star"></i> Penilaian Pengguna</a></li>
            <li><a href="/CODINGAN/z-yakinlogout/formyakin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</aside>
<div class="container">
    <h1>Daftar Peminjaman</h1>
    <!-- Form Filter -->
    <form action="" method="GET" class="filter-form">
        <label for="filter">Filter:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <option value="dipinjam" <?= $filter === 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
            <option value="dikembalikan" <?= $filter === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
            <option value="denda" <?= $filter === 'denda' ? 'selected' : '' ?>>Kena Denda</option>
            <option value="pengajuan" <?= $filter === 'pengajuan' ? 'selected' : '' ?>>Pengajuan Pengembalian</option>
        </select>
        <input type="text" name="search" placeholder="Cari judul atau username..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Cari</button>
    </form>
    <a href="peminjaman_create.php" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Tambah Peminjaman
    </a>
    <!-- Tabel Peminjaman -->
    <table class="peminjaman-table">
        <thead>
            <tr>
                <th>ID Peminjaman</th>
                <th>Username Anggota</th>
                <th>Judul Buku</th>
                <th>Tipe Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Batas Pengembalian</th>
                <th>Status</th>
                <th>Denda</th>
                <th>Status Pengajuan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($peminjaman)): ?>
                <tr>
                    <td colspan="10" style="text-align:center;" class="no-data">Tidak ada data peminjaman.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($peminjaman as $pinjam): ?>
                    <tr>
                        <td><?= htmlspecialchars($pinjam['id']) ?></td>
                        <td><?= htmlspecialchars($pinjam['username']) ?></td>
                        <td><?= htmlspecialchars($pinjam['judul']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($pinjam['tipe_buku'])) ?></td>
                        <td><?= htmlspecialchars($pinjam['tanggal_pinjam']) ?></td>
                        <td><?= htmlspecialchars($pinjam['batas_pengembalian']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($pinjam['status'])) ?></td>
                        <td><?= !empty($pinjam['denda_status']) && $pinjam['denda_status'] === 'belum_dibayar' ? '<span class="denda">' . htmlspecialchars($pinjam['denda_status']) . '</span>' : '-' ?></td>
                        <td><?= htmlspecialchars(ucfirst($pinjam['status_pengajuan'] ?? '-')) ?></td>
                        <td class="actions">
                            <?php if ($pinjam['status_pengajuan'] === 'menunggu' && $pinjam['status'] === 'dipinjam'): ?>
                                <!-- Tombol Terima Pengajuan -->
                                <a href="process_peminjaman.php?id=<?= $pinjam['id'] ?>" class="btn btn-primary">Terima Pengajuan</a>
                                <!-- Tombol Tolak Pengajuan -->
                                <a href="tolak_pengembalian.php?id=<?= $pinjam['id'] ?>" class="btn btn-danger">Tolak Pengajuan</a>
                            <?php elseif ($pinjam['status_pengajuan'] === 'diterima' && $pinjam['status'] === 'dikembalikan'): ?>
                                <span class="status-success">Buku telah dikembalikan</span>
                            <?php endif; ?>
                            <a href="peminjaman_edit.php?id=<?= $pinjam['id'] ?>" class="btn btn-warning">Edit</a>
                            <a href="peminjaman_delete.php?id=<?= $pinjam['id'] ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>