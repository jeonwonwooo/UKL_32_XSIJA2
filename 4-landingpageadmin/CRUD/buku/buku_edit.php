<?php
include 'formkoneksi.php';

// Ambil ID buku dari URL (jika ada)
$id = $_GET['id'] ?? '';
$buku = null;
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM buku WHERE id = ?");
    $stmt->execute([$id]);
    $buku = $stmt->fetch();
}

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
    $existing_file_path = $_POST['existing_file_path'];

    $upload_dir = "../../uploads/";

    // Gambar Cover (Kalau Ganti)
    $gambar_name = $buku ? $buku['gambar'] : null;
    if (!empty($_FILES['gambar']['name'])) {
        $gambar_ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed_image_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($gambar_ext, $allowed_image_ext)) {
            die("Error: Format gambar harus JPG, JPEG, PNG, atau GIF.");
        }

        $gambar_name = uniqid() . '_' . basename($_FILES['gambar']['name']);
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar_name)) {
            if ($buku && !empty($buku['gambar']) && file_exists($upload_dir . $buku['gambar'])) {
                unlink($upload_dir . $buku['gambar']); // Hapus gambar lama
            }
        } else {
            die("Error: Gagal mengupload gambar.");
        }
    }

    // File eBook (Kalau Ganti)
    $file_path = $buku ? $buku['file_path'] : null; // Tetap gunakan file_path lama jika tidak ada perubahan
    if ($tipe_buku === 'Buku Elektronik' && !empty($_FILES['file_ebook']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['file_ebook']['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'pdf') {
            die("Error: File harus dalam format PDF.");
        }

        $file_name = uniqid() . '_' . basename($_FILES['file_ebook']['name']);
        if (move_uploaded_file($_FILES['file_ebook']['tmp_name'], $upload_dir . $file_name)) {
            if ($buku && !empty($buku['file_path']) && file_exists("../../" . $buku['file_path'])) {
                unlink("../../" . $buku['file_path']); // Hapus file eBook lama
            }
            $file_path = "uploads/" . $file_name;
        } else {
            die("Error: Gagal mengupload file eBook.");
        }
    }

    try {
        if ($buku) {
            // Update data buku
            $stmt = $conn->prepare("UPDATE buku 
                                    SET judul = ?, penulis = ?, tahun_terbit = ?, jumlah_halaman = ?, deskripsi = ?, gambar = ?, kategori_id = ?, stok = ?, tipe_buku = ?, isbn = ?, file_path = ? 
                                    WHERE id = ?");
            $stmt->execute([$judul, $penulis, $tahun_terbit, $jumlah_halaman, $deskripsi, $gambar_name, $kategori_id, $stok, $tipe_buku, $isbn, $file_path, $id]);
        } else {
            // Insert data buku baru
            $stmt = $conn->prepare("INSERT INTO buku (judul, penulis, tahun_terbit, jumlah_halaman, deskripsi, gambar, kategori_id, stok, tipe_buku, isbn, file_path)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $penulis, $tahun_terbit, $jumlah_halaman, $deskripsi, $gambar_name, $kategori_id, $stok, $tipe_buku, $isbn, $file_path]);
        }

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
    <title><?= $buku ? 'Edit Buku' : 'Tambah Buku' ?></title>
    <link rel="stylesheet" href="buku_edit.css">
</head>
<body>
    <div class="container mt-5">
        <h1><?= $buku ? 'Edit Buku' : 'Tambah Buku' ?></h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul">Judul</label>
                <input type="text" name="judul" value="<?= $buku ? htmlspecialchars($buku['judul']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="penulis">Penulis</label>
                <input type="text" name="penulis" value="<?= $buku ? htmlspecialchars($buku['penulis']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="tahun_terbit">Tahun Terbit</label>
                <input type="number" name="tahun_terbit" value="<?= $buku ? htmlspecialchars($buku['tahun_terbit']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="jumlah_halaman">Jumlah Halaman</label>
                <input type="number" name="jumlah_halaman" value="<?= $buku ? htmlspecialchars($buku['jumlah_halaman']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" rows="5" required><?= $buku ? htmlspecialchars($buku['deskripsi']) : '' ?></textarea>
            </div>

            <div class="mb-3">
                <label for="gambar">Gambar Cover</label>
                <input type="file" name="gambar">
                <?php if ($buku && $buku['gambar']): ?>
                    <p>Gambar saat ini: <img src="../../uploads/<?= htmlspecialchars($buku['gambar']) ?>" width="100"></p>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="kategori_id">Pilih Kategori</label>
                <select name="kategori_id" required>
                    <?php
                    $stmt = $conn->query("SELECT id, nama_kategori FROM kategori");
                    while ($category = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>" <?= ($buku && $category['id'] == $buku['kategori_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="stok">Stok</label>
                <input type="number" name="stok" value="<?= $buku ? htmlspecialchars($buku['stok']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="tipe_buku">Tipe Buku</label>
                <select name="tipe_buku" id="tipe_buku" required>
                    <option value="" disabled <?= !$buku ? 'selected' : '' ?>>Pilih tipe buku...</option>
                    <?php
                    $tipe_buku_options = ['Buku Fisik', 'Buku Elektronik'];
                    foreach ($tipe_buku_options as $tipe): ?>
                        <option value="<?= htmlspecialchars($tipe) ?>" <?= ($buku && $buku['tipe_buku'] === $tipe) ? 'selected' : '' ?>>
                            <?= ucfirst(htmlspecialchars($tipe)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3 ebook-file" style="display: <?= ($buku && $buku['tipe_buku'] === 'Buku Elektronik') ? 'block' : 'none' ?>;">
                <label for="file_ebook">File Ebook (PDF)</label>
                <input type="file" name="file_ebook" accept=".pdf">
                <?php if ($buku && $buku['file_path']): ?>
                    <p>File saat ini: <a href="../../<?= htmlspecialchars($buku['file_path']) ?>" target="_blank">Lihat File</a></p>
                <?php endif; ?>
                <input type="hidden" name="existing_file_path" value="<?= $buku ? htmlspecialchars($buku['file_path']) : '' ?>">
            </div>

            <button type="submit" class="btn btn-primary"><?= $buku ? 'Simpan Perubahan' : 'Tambah Buku' ?></button>
        </form>
    </div>

    <script>
        document.getElementById('tipe_buku').addEventListener('change', function() {
            const ebookFileSection = document.querySelector('.ebook-file');
            ebookFileSection.style.display = (this.value === 'Buku Elektronik') ? 'block' : 'none';
        });

        // Default behavior based on current book type
        document.addEventListener('DOMContentLoaded', function() {
            const tipeBuku = document.getElementById('tipe_buku');
            const ebookFileSection = document.querySelector('.ebook-file');
            ebookFileSection.style.display = (tipeBuku.value === 'Buku Elektronik') ? 'block' : 'none';
        });
    </script>
</body>
</html>