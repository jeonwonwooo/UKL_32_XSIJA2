<?php
include 'formkoneksi.php';

$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Admin tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Jika password diisi, hash. Kalau tidak, gunakan yang lama
    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $password = $admin['password'];
    }

    // Upload foto profil
    $photo_profil = $admin['photo_profil']; // Default pakai foto lama

    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['foto_profil']['name']);
        $file_tmp = $_FILES['foto_profil']['tmp_name'];
        $upload_dir = "../../uploads/";

        // Validasi ekstensi file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            die("Hanya file gambar (JPG, PNG, GIF) yang diperbolehkan.");
        }

        // Buat folder jika belum ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Pindahkan file ke folder uploads
        move_uploaded_file($file_tmp, $upload_dir . $file_name);
        $photo_profil = $file_name; // Update nama foto dengan yang baru
    }

    try {
        // Update data admin ke database
        $stmt = $conn->prepare("UPDATE admin SET nama = ?, username = ?, password = ?, photo_profil = ? WHERE id = ?");
        $stmt->execute([$nama, $username, $password, $photo_profil, $id]);

        // Redirect ke halaman daftar admin
        header("Location: data-admin_list.php");
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
    <title>Edit Admin</title>
    <link rel="stylesheet" href="data-admin_edit.css">
</head>

<body>
    <div class="container">
        <h1>Edit Admin</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" id="nama" name="nama" class="form-control" value="<?= htmlspecialchars($admin['nama']) ?>" placeholder="Masukkan nama lengkap" required>
            </div>
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
            </div>

            <div class="form-group">
                <label for="foto_profil" class="form-label">Foto Profil</label>
                <div class="file-input-container">
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*" class="form-control">
                    <label for="foto_profil" class="file-custom">Pilih Foto</label>
                </div>
                <?php if ($admin['photo_profil']): ?>
                    <div style="margin-top: 10px;">
                        <img src="../../uploads/<?= htmlspecialchars($admin['photo_profil']) ?>" alt="Foto Profil" width="50">
                        <span style="color: #fff; font-size: 14px;">Foto Saat Ini</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="data-admin_list.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</body>

</html>