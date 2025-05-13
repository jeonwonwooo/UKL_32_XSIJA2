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
    $stok = $_POST['stok'];
    $tipe_buku = $_POST['tipe_buku'];
    $isbn = trim($_POST['isbn']);
    $kategori = $_POST['kategori']; // ✅ Digunakan sebagai ENUM
    $status = $_POST['status'];
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
                                    SET judul = ?, penulis = ?, tahun_terbit = ?, jumlah_halaman = ?, deskripsi = ?, gambar = ?, stok = ?, tipe_buku = ?, isbn = ?, file_path = ?, kategori = ?, status = ?
                                    WHERE id = ?");
            $stmt->execute([
                $judul, $penulis, $tahun_terbit, $jumlah_halaman, $deskripsi, $gambar_name,
                $stok, $tipe_buku, $isbn, $file_path, $kategori, $status, $id
            ]);
        } else {
            // Insert data buku baru
            $stmt = $conn->prepare("INSERT INTO buku (
                judul, penulis, tahun_terbit, jumlah_halaman, deskripsi, gambar, stok, tipe_buku, isbn, file_path, kategori, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $judul, $penulis, $tahun_terbit, $jumlah_halaman, $deskripsi, $gambar_name,
                $stok, $tipe_buku, $isbn, $file_path, $kategori, $status
            ]);
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
                <input type="number" name="jumlah_halaman" value="<?= $buku ? htmlspecialchars($buku['jumlah_halaman']) : '' ?>" min="1" required>
            </div>

            <div class="mb-3">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" rows="5"><?= $buku ? htmlspecialchars($buku['deskripsi']) : '' ?></textarea>
            </div>

            <div class="mb-3">
                <label for="gambar">Gambar Cover</label>
                <input type="file" name="gambar">
                <?php if ($buku && $buku['gambar']): ?>
                    <p>Gambar saat ini: <img src="../../uploads/<?= htmlspecialchars($buku['gambar']) ?>" width="100"></p>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="kategori">Pilih Kategori</label>
                <select name="kategori" required>
                    <option value="" disabled <?= !$buku ? 'selected' : '' ?>>Pilih kategori...</option>
                    <?php
                    $kategori_options = ['Fiksi', 'Non-Fiksi', 'Lainnya'];
                    foreach ($kategori_options as $kat): ?>
                        <option value="<?= htmlspecialchars($kat) ?>" <?= ($buku && $buku['kategori'] == $kat) ? 'selected' : '' ?>>
                            <?= ucfirst(htmlspecialchars($kat)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="stok">Stok</label>
                <input type="number" name="stok" value="<?= $buku ? htmlspecialchars($buku['stok']) : '' ?>" min="0" required>
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

            <div class="mb-3">
                <label for="status">Status</label>
                <select name="status" required>
                    <option value="" disabled <?= !$buku ? 'selected' : '' ?>>Pilih status...</option>
                    <?php
                    $status_options = ['tersedia', 'dipinjam', 'habis'];
                    foreach ($status_options as $sts): ?>
                        <option value="<?= $sts ?>" <?= ($buku && $buku['status'] == $sts) ? 'selected' : '' ?>>
                            <?= ucfirst(htmlspecialchars($sts)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="isbn">ISBN</label>
                <input type="text" name="isbn" value="<?= $buku ? htmlspecialchars($buku['isbn']) : '' ?>" required>
            </div>

            <button type="submit" class="btn btn-primary"><?= $buku ? 'Simpan Perubahan' : 'Tambah Buku' ?></button>
        </form>
    </div>

    <script>
        document.getElementById('tipe_buku').addEventListener('change', function () {
            document.querySelector('.ebook-file').style.display = (this.value === 'Buku Elektronik') ? 'block' : 'none';
        });

        document.addEventListener('DOMContentLoaded', function () {
            const tipeBuku = document.getElementById('tipe_buku');
            document.querySelector('.ebook-file').style.display = (tipeBuku.value === 'Buku Elektronik') ? 'block' : 'none';
        });
    </script>
</body>
</html>