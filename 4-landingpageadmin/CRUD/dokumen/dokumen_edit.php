<?php
include 'formkoneksi.php';

// Ambil ID dokumen dari URL (jika ada)
$id = $_GET['id'] ?? '';
$dokumen = null;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM dokumen WHERE id = ?");
    $stmt->execute([$id]);
    $dokumen = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $tipe_dokumen = $_POST['tipe_dokumen'];
    $deskripsi = trim($_POST['deskripsi']);
    $kategori_id = $_POST['kategori_id'];
    $status = $_POST['status'];

    $upload_dir = "../../uploads/";

    // Upload file dokumen baru (PDF)
    $file_path = $dokumen ? $dokumen['file_path'] : null;
    if (!empty($_FILES['file_path']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'pdf') {
            die("Error: File harus dalam format PDF.");
        }

        $file_name = uniqid() . '_' . basename($_FILES['file_path']['name']);
        if (move_uploaded_file($_FILES['file_path']['tmp_name'], $upload_dir . $file_name)) {
            if ($dokumen && !empty($dokumen['file_path']) && file_exists("../../" . $dokumen['file_path'])) {
                unlink("../../" . $dokumen['file_path']); // Hapus file lama
            }
            $file_path = "uploads/" . $file_name;
        } else {
            die("Error: Gagal mengupload file dokumen.");
        }
    }

    try {
        if ($dokumen) {
            // Update data dokumen
            $stmt = $conn->prepare("UPDATE dokumen 
                                    SET judul = ?, penulis = ?, tahun_terbit = ?, tipe_dokumen = ?, deskripsi = ?, kategori_id = ?, status = ?, file_path = ? 
                                    WHERE id = ?");
            $stmt->execute([$judul, $penulis, $tahun_terbit, $tipe_dokumen, $deskripsi, $kategori_id, $status, $file_path, $id]);
        } else {
            // Insert data dokumen baru
            $stmt = $conn->prepare("INSERT INTO dokumen (judul, penulis, tahun_terbit, tipe_dokumen, deskripsi, kategori_id, status, file_path)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $penulis, $tahun_terbit, $tipe_dokumen, $deskripsi, $kategori_id, $status, $file_path]);
        }

        header("Location: dokumen_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $dokumen ? 'Edit Dokumen' : 'Tambah Dokumen' ?></title>
    <link rel="stylesheet" href="buku_edit.css">
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
            <li><a href="/CODINGAN/z-yakinlogout/formyakinadm.html">Logout</a></li>
        </ul>
    </nav>
</aside>
<div class="container mt-5">
    <h1><?= $dokumen ? 'Edit Dokumen' : 'Tambah Dokumen' ?></h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="judul">Judul</label>
            <input type="text" name="judul" value="<?= $dokumen ? htmlspecialchars($dokumen['judul']) : '' ?>" required>
        </div>

        <div class="mb-3">
            <label for="penulis">Penulis</label>
            <input type="text" name="penulis" value="<?= $dokumen ? htmlspecialchars($dokumen['penulis']) : '' ?>" required>
        </div>

        <div class="mb-3">
            <label for="tahun_terbit">Tahun Terbit</label>
            <input type="number" name="tahun_terbit" value="<?= $dokumen ? htmlspecialchars($dokumen['tahun_terbit']) : '' ?>" min="1900" max="2100">
        </div>

        <div class="mb-3">
            <label for="tipe_dokumen">Tipe Dokumen</label>
            <select name="tipe_dokumen" required>
                <option value="" disabled <?= !$dokumen ? 'selected' : '' ?>>Pilih tipe dokumen...</option>
                <?php
                $tipe_options = ['modul', 'karya_murid', 'tugas_akhir', 'makalah', 'laporan', 'jurnal', 'artikel_konferensi', 'lainnya'];
                foreach ($tipe_options as $tipe): ?>
                    <option value="<?= htmlspecialchars($tipe) ?>" <?= ($dokumen && $dokumen['tipe_dokumen'] === $tipe) ? 'selected' : '' ?>>
                        <?= ucfirst(str_replace('_', ' ', htmlspecialchars($tipe))) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="deskripsi">Deskripsi</label>
            <textarea name="deskripsi" rows="5"><?= $dokumen ? htmlspecialchars($dokumen['deskripsi']) : '' ?></textarea>
        </div>

        <div class="mb-3">
            <label for="kategori_id">Pilih Kategori</label>
            <select name="kategori_id" required>
                <option value="" disabled selected>Pilih kategori...</option>
                <?php
                $stmt = $conn->query("SELECT id, nama_kategori FROM kategori");
                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= htmlspecialchars($category['id']) ?>" <?= ($dokumen && $category['id'] == $dokumen['kategori_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="file_path">Upload File Dokumen (PDF)</label>
            <input type="file" name="file_path" accept=".pdf">
            <?php if ($dokumen && $dokumen['file_path']): ?>
                <p>File saat ini: <a href="../../<?= htmlspecialchars($dokumen['file_path']) ?>" target="_blank">Lihat File</a></p>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="status">Status</label>
            <select name="status" required>
                <option value="" disabled <?= !$dokumen ? 'selected' : '' ?>>Pilih status...</option>
                <option value="tersedia" <?= ($dokumen && $dokumen['status'] === 'tersedia') ? 'selected' : '' ?>>Tersedia</option>
                <option value="tidak tersedia" <?= ($dokumen && $dokumen['status'] === 'tidak tersedia') ? 'selected' : '' ?>>Tidak Tersedia</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><?= $dokumen ? 'Simpan Perubahan' : 'Tambah Dokumen' ?></button>
    </form>
</div>
</body>
</html>