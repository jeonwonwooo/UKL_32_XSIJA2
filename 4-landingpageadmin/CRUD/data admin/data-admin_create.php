<?php
include 'formkoneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    $foto_profil = null;
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['foto_profil']['name']);
        $file_tmp = $_FILES['foto_profil']['tmp_name'];
        $upload_dir = "/CODINGAN/4-landingpageadmin/uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        move_uploaded_file($file_tmp, $upload_dir . $file_name);
        $foto_profil = $file_name;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO admin (nama, username, password, foto_profil) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $username, $password, $foto_profil]);

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
    <title>Tambah Admin</title>
    <link rel="stylesheet" href="data-admin_create.css">
</head>

<body>
    <div class="container">
        <h1>Tambah Admin</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="foto_profil">Foto Profil</label>
                <div class="file-input-container">
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*">
                    <span class="file-custom">Pilih File...</span>
                </div>
                <small class="text-muted">Biarkan kosong jika tidak ingin mengupload foto.</small>
            </div>
            <div class="form-group" id="crop-area" style="display: none;">
                <label>Atur Ulang Foto Profil</label>
                <div style="width: 100%; max-width: 300px; margin: 0 auto;">
                    <img id="image-preview" src="#" alt="Preview" style="max-width: 100%;">
                </div>
                <button type="button" id="crop-button" class="btn btn-primary" style="margin-top: 10px;">Crop dan Simpan</button>
            </div>
            <div class="button-group">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="data-admin_list.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</body>

</html>