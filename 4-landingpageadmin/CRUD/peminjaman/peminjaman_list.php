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
    SELECT p.id, a.username, b.judul, b.tipe_buku, p.tanggal_pinjam, p.batas_pengembalian, p.status, 
           CASE 
               WHEN p.status = 'dipinjam' AND CURDATE() > p.batas_pengembalian THEN 'Kena Denda'
               ELSE ''
           END AS denda_status
    FROM peminjaman p
    JOIN anggota a ON p.anggota_id = a.id
    JOIN buku b ON p.buku_id = b.id
";

// Tambahkan kondisi filter
if ($filter === 'fisik') {
    $query .= " WHERE b.tipe_buku = 'fisik'";
} elseif ($filter === 'ebook') {
    $query .= " WHERE b.tipe_buku = 'ebook'";
} elseif ($filter === 'jurnal') {
    $query .= " WHERE b.tipe_buku = 'jurnal'";
} elseif ($filter === 'dipinjam') {
    $query .= " WHERE p.status = 'dipinjam'";
} elseif ($filter === 'dikembalikan') {
    $query .= " WHERE p.status = 'dikembalikan'";
} elseif ($filter === 'denda') {
    $query .= " WHERE p.status = 'dipinjam' AND CURDATE() > p.batas_pengembalian";
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
    $stmt->bind_param("ss", $searchParam, $searchParam); // Binding dua placeholder
}

// Eksekusi query
try {
    $stmt->execute();
    $result = $stmt->get_result();
    $peminjaman = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peminjaman</title>
    <link rel="stylesheet" href="peminjaman_list.css">
</head>

<body>
    <div class="container">
        <h1>Daftar Peminjaman</h1>

        <!-- Form Filter -->
        <form action="" method="GET" class="filter-form">
            <label for="filter">Filter:</label>
            <select name="filter" id="filter" onchange="this.form.submit()">
                <option value="semua" <?= $filter === 'semua' ? 'selected' : '' ?>>Semua</option>
                <option value="fisik" <?= $filter === 'fisik' ? 'selected' : '' ?>>Fisik</option>
                <option value="ebook" <?= $filter === 'ebook' ? 'selected' : '' ?>>Ebook</option>
                <option value="jurnal" <?= $filter === 'jurnal' ? 'selected' : '' ?>>Jurnal</option>
                <option value="dipinjam" <?= $filter === 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                <option value="dikembalikan" <?= $filter === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                <option value="denda" <?= $filter === 'denda' ? 'selected' : '' ?>>Kena Denda</option>
            </select>

            <input type="text" name="search" placeholder="Cari judul atau username..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Cari</button>
        </form>

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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($peminjaman)): ?>
                    <tr>
                        <td colspan="9" class="no-data">Tidak ada data peminjaman.</td>
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
                            <td><?= !empty($pinjam['denda_status']) ? '<span class="denda">' . htmlspecialchars($pinjam['denda_status']) . '</span>' : '-' ?></td>
                            <td class="actions">
                                <?php if ($pinjam['status'] === 'dipinjam'): ?>
                                    <a href="process_peminjaman.php?id=<?= $pinjam['id'] ?>&action=kembalikan" class="btn btn-success">Kembalikan</a>
                                <?php endif; ?>
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