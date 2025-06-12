<?php
include 'formkoneksi.php';

// Ambil parameter filter dan pencarian dari URL
$filter = $_GET['filter'] ?? 'semua';
$search = $_GET['search'] ?? '';

// Query dasar
$query = "
    SELECT 
        f.id, 
        f.user_id, 
        f.buku_id, 
        f.dokumen_id, 
        f.created_at, 
        a.username, 
        b.judul AS judul_buku, 
        d.judul AS judul_dokumen
    FROM favorit f
    JOIN anggota a ON f.user_id = a.id
    LEFT JOIN buku b ON f.buku_id = b.id
    LEFT JOIN dokumen d ON f.dokumen_id = d.id
";

$conditions = [];
$params = [];

// Tambahkan kondisi filter
if ($filter === 'buku') {
    $conditions[] = "f.buku_id IS NOT NULL";
} elseif ($filter === 'dokumen') {
    $conditions[] = "f.dokumen_id IS NOT NULL";
}

// Tambahkan pencarian
if (!empty($search)) {
    $conditions[] = "(a.username LIKE ? OR b.judul LIKE ? OR d.judul LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Gabungkan kondisi
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Persiapkan statement
$stmt = $conn->prepare($query);

// Bind parameter pencarian jika ada
if (!empty($params)) {
    foreach ($params as $key => $param) {
        $stmt->bindValue($key + 1, $param, PDO::PARAM_STR);
    }
}

// Eksekusi query
$stmt->execute();
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="favorit_list.css">
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
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/dokumen/dokumen_list.php"><i class="fas fa-file-alt"></i> Daftar Dokumen</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/favorit/favorit_list.php" class="active"><i class="fas fa-heart"></i> Favorit Pengguna</a></li>
            <li><a href="/CODINGAN/4-landingpageadmin/CRUD/rating-ulasan/rating-ulasan_list.php"><i class="fas fa-star"></i> Penilaian Pengguna</a></li>
            <li><a href="/CODINGAN/z-yakinlogout/formyakin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</aside>
    <div class="container">
        <h1><i class="fas fa-heart"></i> Daftar Favorit</h1>

        <!-- Filter and Search Bar -->
<div class="filter-bar">
    <form method="GET" class="filter-form">
        <!-- Filter -->
        <label for="filter">Filter:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <option value="semua" <?= ($filter === 'semua') ? 'selected' : '' ?>>Semua</option>
            <option value="buku" <?= ($filter === 'buku') ? 'selected' : '' ?>>Buku</option>
            <option value="dokumen" <?= ($filter === 'dokumen') ? 'selected' : '' ?>>Dokumen</option>
        </select>

        <!-- Pencarian -->
        <input type="text" name="search" placeholder="Cari berdasarkan nama anggota, judul buku, atau judul dokumen..." value="<?= htmlspecialchars($search) ?>">

        <!-- Tombol Cari -->
        <button type="submit">Cari</button>
    </form>
</div>

        <!-- Table -->
        <table class="custom-table">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-user"></i> Pengguna</th>
                    <th><i class="fas fa-book"></i> Buku / Dokumen</th>
                    <th><i class="fas fa-calendar-alt"></i> Dibuat Pada</th>
                    <th><i class="fas fa-cogs"></i> Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($favorites)): ?>
                    <?php foreach ($favorites as $favorite): ?>
                        <tr>
                            <td><?= htmlspecialchars($favorite['id']) ?></td>
                            <td><?= htmlspecialchars($favorite['username']) ?></td>
                            <td>
                                <?php
                                if (!empty($favorite['judul_buku'])) {
                                    echo '<span class="badge badge-buku"><i class="fas fa-book"></i> ' . htmlspecialchars($favorite['judul_buku']) . '</span>';
                                } elseif (!empty($favorite['judul_dokumen'])) {
                                    echo '<span class="badge badge-dokumen"><i class="fas fa-file-alt"></i> ' . htmlspecialchars($favorite['judul_dokumen']) . '</span>';
                                } else {
                                    echo '<em>Tidak ada</em>';
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($favorite['created_at']) ?></td>
                            <td>
                                <a href="process_favorit.php?id=<?= $favorite['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Tidak ada data favorit.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>