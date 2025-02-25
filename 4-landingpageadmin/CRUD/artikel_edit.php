<?php
include '../../formkoneksi.php';

$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM artikel WHERE id = ?");
$stmt->execute([$id]);
$artikel = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$artikel) {
    die("Artikel tidak ditemukan.");
}

$stmt = $conn->query("SELECT id, nama FROM admin");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $konten = trim($_POST['konten']);
    $tanggal_publikasi = $_POST['tanggal_publikasi'];
    $admin_id = $_POST['admin_id'];

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['gambar']['name']);
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $upload_dir = "../../uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        move_uploaded_file($file_tmp, $upload_dir . $file_name);

        if ($artikel['gambar']) {
            unlink($upload_dir . $artikel['gambar']);
        }
    } else {
        $file_name = $artikel['gambar'];
    }

    try {
        $stmt = $conn->prepare("UPDATE artikel SET judul = ?, konten = ?, gambar = ?, tanggal_publikasi = ?, admin_id = ? WHERE id = ?");
        $stmt->execute([$judul, $konten, $file_name, $tanggal_publikasi, $admin_id, $id]);

        header("Location: artikel_list.php");
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
    <title>Edit Artikel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Edit Artikel</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($artikel['judul']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="konten" class="form-label">Konten</label>
                <textarea class="form-control" id="konten" name="konten" rows="5" required><?= htmlspecialchars($artikel['konten']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar</label>
                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar.</small>
            </div>
            <div class="mb-3">
                <label for="tanggal_publikasi" class="form-label">Tanggal Publikasi</label>
                <input type="date" class="form-control" id="tanggal_publikasi" name="tanggal_publikasi" value="<?= htmlspecialchars($artikel['tanggal_publikasi']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="admin_id" class="form-label">Pilih Admin</label>
                <select class="form-select" id="admin_id" name="admin_id" required>
                    <?php foreach ($admins as $admin): ?>
                        <option value="<?= htmlspecialchars($admin['id']) ?>" <?= ($admin['id'] == $artikel['admin_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($admin['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>