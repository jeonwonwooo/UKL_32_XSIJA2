<?php
include '../../formkoneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $konten = trim($_POST['konten']);
    $tanggal_publikasi = $_POST['tanggal_publikasi'];
    $admin_id = $_POST['admin_id'];
    $status = 'draft'; // Default status adalah draft

    // Upload gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['gambar']['name']);
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $upload_dir = "../../uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Buat folder uploads jika belum ada
        }

        move_uploaded_file($file_tmp, $upload_dir . $file_name);
    } else {
        die("Error uploading image.");
    }

    try {
        $stmt = $conn->prepare("INSERT INTO artikel (judul, konten, gambar, tanggal_publikasi, admin_id, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $konten, $file_name, $tanggal_publikasi, $admin_id, $status]);

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
    <title>Tambah Artikel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Tambah Artikel</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" class="form-control" id="judul" name="judul" required>
            </div>
            <div class="mb-3">
                <label for="konten" class="form-label">Konten</label>
                <textarea class="form-control" id="konten" name="konten" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="gambar" class="form-label">Gambar</label>
                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" required>
            </div>
            <div class="mb-3">
                <label for="tanggal_publikasi" class="form-label">Tanggal Publikasi</label>
                <input type="date" class="form-control" id="tanggal_publikasi" name="tanggal_publikasi" required>
            </div>
            <div class="mb-3">
                <label for="admin_id" class="form-label">Pilih Admin</label>
                <select class="form-select" id="admin_id" name="admin_id" required>
                    <?php
                    $stmt = $conn->query("SELECT id, nama FROM admin");
                    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($admins as $admin): ?>
                        <option value="<?= htmlspecialchars($admin['id']) ?>">
                            <?= htmlspecialchars($admin['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Sebagai Draft</button>
            <a href="/CODINGAN/4-landingpageadmin/landingpage/beranda/beranda.html" class="btn btn-secondary">Kembali ke Beranda</a>
        </form>
    </div>
</body>
</html>