<?php
include 'formkoneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $tipe_dokumen = $_POST['tipe_dokumen'];
    $deskripsi = trim($_POST['deskripsi']);
    $kategori_id = $_POST['kategori_id'];
    $admin_id = 1; // contoh, bisa diambil dari session login
    $status = $_POST['status'];

    $upload_dir = "../../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Upload file dokumen (PDF)
    $file_name = null;
    if (!empty($_FILES['file_path']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'pdf') {
            die("Error: File harus dalam format PDF.");
        }

        $file_name = uniqid() . '_' . basename($_FILES['file_path']['name']);
        if (!move_uploaded_file($_FILES['file_path']['tmp_name'], $upload_dir . $file_name)) {
            die("Error: Gagal mengupload file dokumen.");
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO dokumen (judul, penulis, tahun_terbit, tipe_dokumen, deskripsi, kategori_id, admin_id, status, file_path)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $penulis, $tahun_terbit, $tipe_dokumen, $deskripsi, $kategori_id, $admin_id, $status, $file_name]);

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
    <title>Tambah Dokumen</title>
    <link rel="stylesheet" href="buku_create.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Dokumen</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul">Judul</label>
                <input type="text" name="judul" required>
            </div>

            <div class="mb-3">
                <label for="penulis">Penulis</label>
                <input type="text" name="penulis" required>
            </div>

            <div class="mb-3">
                <label for="tahun_terbit">Tahun Terbit</label>
                <input type="number" name="tahun_terbit" required>
            </div>

            <div class="mb-3">
                <label for="tipe_dokumen">Tipe Dokumen</label>
                <select name="tipe_dokumen" required>
                    <option value="" disabled selected>Pilih tipe dokumen...</option>
                    <?php
                    // Daftar tipe dokumen sesuai ENUM dari tabel dokumen
                    $tipe_options = ['modul', 'karya_murid', 'tugas_akhir', 'makalah', 'laporan', 'jurnal', 'artikel_konferensi', 'lainnya'];
                    foreach ($tipe_options as $tipe): ?>
                        <option value="<?= htmlspecialchars($tipe) ?>">
                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($tipe))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" rows="5" required></textarea>
            </div>

            <div class="mb-3">
                <label for="kategori_id">Pilih Kategori</label>
                <select name="kategori_id" required>
                    <option value="" disabled selected>Pilih kategori...</option>
                    <?php
                    $stmt = $conn->query("SELECT id, nama_kategori FROM kategori");
                    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>">
                            <?= htmlspecialchars($category['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="file_path">Upload File Dokumen (PDF)</label>
                <input type="file" name="file_path" accept=".pdf" required>
            </div>

            <div class="mb-3">
                <label for="status">Status</label>
                <select name="status" required>
                    <option value="" disabled selected>Pilih status...</option>
                    <option value="tersedia">Tersedia</option>
                    <option value="tidak tersedia">Tidak Tersedia</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Dokumen</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='dokumen_list.php'">Kembali ke Daftar Dokumen</button>
        </form>
    </div>
</body>
</html>