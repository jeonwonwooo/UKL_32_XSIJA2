<?php
include 'formkoneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $jumlah_halaman = $_POST['jumlah_halaman'];
    $deskripsi = trim($_POST['deskripsi']);
    $gambar_name = null;
    $file_path = null;
    $stok = $_POST['stok'];
    $tipe_buku = $_POST['tipe_buku'];
    $isbn = trim($_POST['isbn']);
    $status = $_POST['status'];
    $kategori = $_POST['kategori']; // Baru ditambahkan

    $upload_dir = "../../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Upload gambar cover
    if (!empty($_FILES['gambar']['name'])) {
        $gambar_ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed_image_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($gambar_ext, $allowed_image_ext)) {
            die("Error: Format gambar harus JPG, JPEG, PNG, atau GIF.");
        }

        $gambar_name = uniqid() . '_' . basename($_FILES['gambar']['name']);
        if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar_name)) {
            die("Error: Gagal mengupload gambar.");
        }
    }

    // Upload file ebook jika tipe buku adalah ebook
    if ($tipe_buku === 'Buku Elektronik' && !empty($_FILES['file_ebook']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['file_ebook']['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'pdf') {
            die("Error: File harus dalam format PDF.");
        }

        $file_name = uniqid() . '_' . basename($_FILES['file_ebook']['name']);
        if (!move_uploaded_file($_FILES['file_ebook']['tmp_name'], $upload_dir . $file_name)) {
            die("Error: Gagal mengupload file eBook.");
        }

        $file_path = "uploads/" . $file_name;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO buku (
            judul, penulis, tahun_terbit, jumlah_halaman, deskripsi, gambar, stok, tipe_buku, isbn, status, file_path, kategori
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $judul, $penulis, $tahun_terbit, $jumlah_halaman, $deskripsi, $gambar_name, $stok, $tipe_buku, $isbn, $status, $file_path, $kategori
        ]);

        header("Location: buku_list.php");
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
    <title>Tambah Buku</title>
    <link rel="stylesheet" href="buku_create.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Buku</h1>
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
                <label for="jumlah_halaman">Jumlah Halaman</label>
                <input type="number" name="jumlah_halaman" min="1" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" rows="5" required></textarea>
            </div>

            <div class="mb-3">
                <label for="gambar">Gambar Cover</label>
                <input type="file" name="gambar" accept="image/*" required>
            </div>

            <div class="mb-3">
                <label for="kategori">Pilih Kategori</label>
                <select name="kategori" required>
                    <option value="" disabled selected>Pilih kategori...</option>
                    <?php
                    $kategori_options = ['Fiksi', 'Non-Fiksi', 'Lainnya'];
                    foreach ($kategori_options as $kategori): ?>
                        <option value="<?= htmlspecialchars($kategori) ?>">
                            <?= ucfirst(htmlspecialchars($kategori)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="stok">Stok</label>
                <input type="number" name="stok" min="0" required>
            </div>

            <div class="mb-3">
                <label for="tipe_buku">Tipe Buku</label>
                <select name="tipe_buku" id="tipe_buku" required>
                    <option value="" disabled selected>Pilih tipe buku...</option>
                    <?php
                    $tipe_buku_options = ['Buku Fisik', 'Buku Elektronik'];
                    foreach ($tipe_buku_options as $tipe): ?>
                        <option value="<?= htmlspecialchars($tipe) ?>">
                            <?= ucfirst(htmlspecialchars($tipe)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 ebook-file" style="display: none;">
                <label for="file_ebook">File Ebook (PDF)</label>
                <input type="file" name="file_ebook" accept=".pdf">
            </div>

            <div class="mb-3">
                <label for="status">Status</label>
                <select name="status" required>
                    <option value="" disabled selected>Pilih status...</option>
                    <option value="tersedia">Tersedia</option>
                    <option value="dipinjam">Dipinjam</option>
                    <option value="habis">Habis</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="isbn">ISBN</label>
                <input type="text" name="isbn" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Buku</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='buku_list.php'">Kembali ke Daftar Buku</button>
        </form>
    </div>

    <script>
        document.getElementById('tipe_buku').addEventListener('change', function() {
            document.querySelector('.ebook-file').style.display = (this.value === 'Buku Elektronik') ? 'block' : 'none';
        });
    </script>
</body>
</html>