<?php
include 'formkoneksi.php';

// Debugging: Pastikan $conn ada
if (!$conn) {
    die("Koneksi database tidak tersedia.");
}

// Ambil semua data denda
$query = "
    SELECT * FROM denda
";
$stmt = $conn->prepare($query);
$stmt->execute();
$denda = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Denda</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <link rel="stylesheet" href="denda_list.css">
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
        <h1>Daftar Denda</h1>
        <a href="denda_create.php" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Tambah Denda
        </a>
        <!-- Tabel Denda -->
        <table class="denda-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Anggota</th>
                    <th>ID Peminjaman</th>
                    <th>Nominal</th>
                    <th>Status</th>
                    <th>Tanggal Denda</th>
                    <th>Keterangan</th>
                    <th>Bukti Pembayaran</th>
                    <th>Status Pembayaran</th>
                    <th>Tanggal Pembayaran</th>
                    <th>Metode Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($denda)): ?>
                    <tr>
                        <td colspan="14" class="no-data">Tidak ada data denda.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($denda as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['id']) ?></td>
                            <td><?= htmlspecialchars($item['anggota_id']) ?></td>
                            <td><?= htmlspecialchars($item['peminjaman_id']) ?></td>
                            <td><?= htmlspecialchars($item['nominal']) ?></td>
                            <td><?= htmlspecialchars($item['status']) ?></td>
                            <td><?= htmlspecialchars($item['tanggal_denda']) ?></td>
                            <td><?= htmlspecialchars(substr($item['keterangan'], 0, 50)) . (strlen($item['keterangan']) > 50 ? '...' : '') ?></td>
                            <td>
    <?php if (!empty($item['bukti_pembayaran'])): ?>
        <!-- Periksa apakah file bukti pembayaran ada -->
        <img src="<?= htmlspecialchars($item['bukti_pembayaran']) ?>" alt="Bukti Pembayaran" width="100">
    <?php else: ?>
        Tidak ada bukti pembayaran
    <?php endif; ?>
</td>
                            <td><?= htmlspecialchars($item['status_pembayaran']) ?></td>
                            <td><?= htmlspecialchars($item['tanggal_pembayaran']) ?: '-' ?></td>
                            <td><?= htmlspecialchars($item['metode_pembayaran']) ?></td>
                            <td class="actions">
                                <!-- Tombol Terima & Tolak hanya muncul jika status_pembayaran pending -->
                                <?php if ($item['status_pembayaran'] === 'pending'): ?>
                                    <a href="denda_terima.php?id=<?= $item['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Terima pembayaran ini?')">Terima</a>
                                    <a href="denda_tolak.php?id=<?= $item['id'] ?>" class="btn btn-warning btn-sm" onclick="return confirm('Tolak pembayaran ini?')">Tolak</a>
                                <?php elseif ($item['status_pembayaran'] === 'success'): ?>
                                    <span class="badge badge-success">Sudah Diterima</span>
                                <?php elseif ($item['status_pembayaran'] === 'failed'): ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php endif; ?>

                                <!-- Tombol Hapus -->
                                <a href="denda_delete.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus denda ini?')">
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