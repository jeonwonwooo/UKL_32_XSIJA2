<?php include 'formkoneksi.php'; ?>

<?php
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
    $status = 'tersedia';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['gambar']['name']);
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $upload_dir = "../../uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        move_uploaded_file($file_tmp, $upload_dir . $file_name);
    } else {
        die("Error uploading image.");
    }

    try {
        $stmt = $conn->prepare("INSERT INTO buku (judul, penulis, tahun_terbit, jumlah_halaman, deskripsi, gambar, kategori_id, stok, tipe_buku, isbn, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $penulis, $tahun_terbit, $jumlah_halaman, $deskripsi, $file_name, $kategori_id, $stok, $tipe_buku, $isbn, $status]);
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
    <title>Tambah Buku</title>
    <link rel="stylesheet" href="buku_create.css">
</head>

<body>
    <div class="container">
        <h1>Tambah Buku</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" class="form-control" id="judul" name="judul" placeholder="Masukkan judul..." required>
            </div>
            <div class="mb-3">
                <label for="penulis" class="form-label">Penulis</label>
                <input type="text" class="form-control" id="penulis" name="penulis" placeholder="Masukkan nama penulis..." required>
            </div>
            <div class="mb-3">
                <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" placeholder="Masukkan tahun terbit..." required>
            </div>
            <div class="mb-3">
                <label for="jumlah_halaman" class="form-label">Jumlah Halaman</label>
                <input type="number" class="form-control" id="jumlah_halaman" name="jumlah_halaman" placeholder="Masukkan jumlah halaman..." required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" placeholder="Tulis deskripsi di sini..." required style="resize: none; overflow-y: auto;"></textarea>
            </div>
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar</label>
                <div class="file-input-container">
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" required>
                    <span class="file-custom">Pilih File...</span>
                </div>
            </div>
            <div class="mb-3">
                <label for="kategori_id" class="form-label">Pilih Kategori</label>
                <select class="form-select" id="kategori_id" name="kategori_id" required>
                    <option value="" disabled selected>Pilih kategori...</option>
                    <?php
                    $stmt = $conn->query("SELECT id, nama_kategori FROM kategori");
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>">
                            <?= htmlspecialchars($category['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="stok" class="form-label">Stok</label>
                <input type="number" class="form-control" id="stok" name="stok" placeholder="Masukkan jumlah stok..." required>
            </div>
            <div class="mb-3">
                <label for="tipe_buku" class="form-label">Tipe Buku</label>
                <select class="form-select" id="tipe_buku" name="tipe_buku" required>
                    <option value="fisik">Fisik</option>
                    <option value="ebook">Ebook</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" class="form-control" id="isbn" name="isbn" placeholder="Masukkan ISBN..." required>
            </div>
            <div class="button-group">
                <button type="submit" class="btn btn-primary">Simpan Buku</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='buku_list.php'">Kembali ke Daftar Buku</button>
            </div>
        </form>
    </div>
</body>

</html>