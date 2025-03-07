<?php include 'formkoneksi.php'; ?>

<?php
// Ambil ID buku dari URL
$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
$stmt->execute([$id]);
$buku = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buku) {
    die("Buku tidak ditemukan.");
}

// Ambil daftar kategori untuk dropdown
$stmt = $conn->query("SELECT id, nama_kategori FROM kategori");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $jumlah_halaman = $_POST['jumlah_halaman'];
    $deskripsi = trim($_POST['deskripsi']);
    $kategori_id = $_POST['kategori_id'];
    $stok = $_POST['stok'];
    $tipe_buku = $_POST['tipe_buku'];
    $isbn = trim($_POST['isbn']);

    // Upload gambar baru jika ada
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['gambar']['name']);
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $upload_dir = "../../uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        move_uploaded_file($file_tmp, $upload_dir . $file_name);

        // Hapus gambar lama jika ada
        if ($buku['gambar']) {
            unlink($upload_dir . $buku['gambar']);
        }
    } else {
        $file_name = $buku['gambar']; // Gunakan gambar lama jika tidak diupload
    }

    try {
        $stmt = $conn->prepare("UPDATE buku SET judul = ?, penulis = ?, tahun_terbit = ?, jumlah_halaman = ?, deskripsi = ?, gambar = ?, kategori_id = ?, stok = ?, tipe_buku = ?, isbn = ? WHERE id = ?");
        $stmt->execute([$judul, $penulis, $tahun_terbit, $jumlah_halaman, $deskripsi, $file_name, $kategori_id, $stok, $tipe_buku, $isbn, $id]);

        header("Location: buku_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1>Edit Buku</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($buku['judul']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="penulis" class="form-label">Penulis</label>
                <input type="text" class="form-control" id="penulis" name="penulis" value="<?= htmlspecialchars($buku['penulis']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" value="<?= htmlspecialchars($buku['tahun_terbit']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="jumlah_halaman" class="form-label">Jumlah Halaman</label>
                <input type="number" class="form-control" id="jumlah_halaman" name="jumlah_halaman" value="<?= htmlspecialchars($buku['jumlah_halaman']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?= htmlspecialchars($buku['deskripsi']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar</label>
                <div class="file-input-container">
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                    <span class="file-custom">Pilih File...</span>
                </div>
                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar.</small>
            </div>
            <div class="mb-3">
                <label for="kategori_id" class="form-label">Pilih Kategori</label>
                <select class="form-select" id="kategori_id" name="kategori_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>" <?= ($category['id'] == $buku['kategori_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="stok" class="form-label">Stok</label>
                <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($buku['stok']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipe_buku" class="form-label">Tipe Buku</label>
                <select class="form-select" id="tipe_buku" name="tipe_buku" required>
                    <option value="fisik" <?= ($buku['tipe_buku'] === 'fisik') ? 'selected' : '' ?>>Fisik</option>
                    <option value="ebook" <?= ($buku['tipe_buku'] === 'ebook') ? 'selected' : '' ?>>Ebook</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" class="form-control" id="isbn" name="isbn" value="<?= htmlspecialchars($buku['isbn']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</body>

</html>