<?php
session_start();
include 'formkoneksi.php';

// Verifikasi admin
if (!isset($_SESSION['admin_id'])) {
    die("Akses ditolak. Harus login sebagai admin.");
}

// Debugging: Pastikan $conn ada
if (!$conn) {
    die("Koneksi database tidak tersedia.");
}

// Ambil parameter filter dan pencarian dari URL
$filter = $_GET['filter'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Query dasar
$query = "
    SELECT 
        d.*, 
        a.username AS anggota_username,
        b.judul AS judul_buku
    FROM 
        denda d
    LEFT JOIN 
        anggota a ON d.anggota_id = a.id
    LEFT JOIN 
        peminjaman p ON d.peminjaman_id = p.id
    LEFT JOIN 
        buku b ON p.buku_id = b.id
";

// Tambahkan kondisi filter
if ($filter === 'belum_dibayar') {
    $query .= " WHERE d.status = 'belum_dibayar'";
} elseif ($filter === 'proses') {
    $query .= " WHERE d.status = 'proses'";
} elseif ($filter === 'sudah_dibayar') {
    $query .= " WHERE d.status = 'sudah_dibayar'";
}

// Tambahkan pencarian
if (!empty($search)) {
    if (strpos($query, 'WHERE') !== false) {
        $query .= " AND (a.username LIKE ? OR b.judul LIKE ?)";
    } else {
        $query .= " WHERE (a.username LIKE ? OR b.judul LIKE ?)";
    }
}

$query .= " ORDER BY d.id ASC";

// Persiapkan statement
$stmt = $conn->prepare($query);

// Bind parameter pencarian jika ada
if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmt->bindValue(1, $searchParam, PDO::PARAM_STR); // Binding pertama
    $stmt->bindValue(2, $searchParam, PDO::PARAM_STR); // Binding kedua
}

// Eksekusi query
$stmt->execute();
$denda = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tangkap pesan status
$status_message = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'diterima':
            $status_message = '<div class="alert alert-success">Pembayaran denda telah diterima.</div>';
            break;
        case 'penolakan_berhasil':
            $status_message = '<div class="alert alert-warning">Pembayaran denda telah ditolak.</div>';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Denda</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">     
    <link rel="stylesheet" href="denda_list.css">
    <style>
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .denda-table img {
            max-width: 100px;
            max-height: 100px;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
    </style>
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
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/denda/denda_list.php" class="active"><i class="fas fa-money-bill-wave"></i> Denda Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/dokumen/dokumen_list.php"><i class="fas fa-file-alt"></i> Daftar Dokumen</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/favorit/favorit_list.php"><i class="fas fa-heart"></i> Favorit Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/rating-ulasan/rating-ulasan_list.php"><i class="fas fa-star"></i> Penilaian Pengguna</a></li>
            <li><a href="/CODINGAN/z-yakinlogout/formyakin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</aside>
<div class="container">
    <h1>Daftar Denda</h1>
    <?= $status_message ?>
    <!-- Filter and Search Bar -->
<div class="filter-bar">
    <!-- Form Filter dan Pencarian -->
    <form method="GET" class="filter-form">
        <label for="filter">Filter:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <option value="semua" <?= $filter === 'semua' ? 'selected' : '' ?>>Semua</option>
            <option value="belum_dibayar" <?= $filter === 'belum_dibayar' ? 'selected' : '' ?>>Belum Dibayar</option>
            <option value="proses" <?= $filter === 'proses' ? 'selected' : '' ?>>Proses</option>
            <option value="sudah_dibayar" <?= $filter === 'sudah_dibayar' ? 'selected' : '' ?>>Sudah Dibayar</option>
        </select>

        <label for="search">Cari:</label>
        <input type="text" name="search" id="search" placeholder="Cari berdasarkan nama anggota atau judul buku..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Cari</button>
    </form>
</div>
    <!-- Table -->
    <table class="denda-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Anggota</th>
                <th>Buku</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Tanggal Denda</th>
                <th>Keterangan</th>
                <th>Bukti Pembayaran</th>
                <th>Status Pembayaran</th>
                <th>Tanggal Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($denda)): ?>
                <tr>
                    <td colspan="11" style="text-align:center;" class="no-data">Tidak ada data denda.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($denda as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['id']) ?></td>
                        <td>
                            <?= htmlspecialchars($item['anggota_username']) ?><br>
                        </td>
                        <td><?= htmlspecialchars($item['judul_buku'] ?? '-') ?></td>
                        <td>Rp<?= number_format($item['nominal'], 0, ',', '.') ?></td>
                        <td>
                            <span class="badge <?= $item['status'] === 'sudah_dibayar' ? 'badge-success' : 'badge-danger' ?>">
                                <?= htmlspecialchars($item['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($item['tanggal_denda']) ?></td>
                        <td><?= htmlspecialchars(substr($item['keterangan'], 0, 30)) . (strlen($item['keterangan']) > 30 ? '...' : '') ?></td>
                        <td>
                            <?php if (!empty($item['bukti_pembayaran'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($item['bukti_pembayaran']) ?>" target="_blank">
                                    <img src="../../uploads/<?= htmlspecialchars($item['bukti_pembayaran']) ?>" alt="Bukti Pembayaran">
                                </a>
                            <?php else: ?>
                                <span class="badge badge-warning">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $badge_class = '';
                            switch ($item['status_pembayaran']) {
                                case 'success': $badge_class = 'badge-success'; break;
                                case 'failed': $badge_class = 'badge-danger'; break;
                                case 'pending': $badge_class = 'badge-warning'; break;
                                default: $badge_class = 'badge-secondary';
                            }
                            ?>
                            <span class="badge <?= $badge_class ?>">
                                <?= htmlspecialchars($item['status_pembayaran']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($item['tanggal_pembayaran'] ?? '-') ?></td>
                        <td class="actions">
                            <?php if ($item['status'] === 'proses'): ?>
                                <a href="denda_terima.php?id=<?= $item['id'] ?>" class="btn btn-success btn-sm" 
                                   onclick="return confirm('Terima pembayaran denda ini?')">
                                    <i class="fas fa-check"></i> Terima
                                </a>
                                <a href="denda_tolak.php?id=<?= $item['id'] ?>" class="btn btn-warning btn-sm"
                                   onclick="return confirm('Tolak pembayaran denda ini?')">
                                    <i class="fas fa-times"></i> Tolak
                                </a>
                            <?php endif; ?>
                            <a href="hapus_denda.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" 
                               onclick="return confirm('Yakin hapus denda ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>