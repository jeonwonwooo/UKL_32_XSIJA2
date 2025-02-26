<?php
include '../../formkoneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $nama = trim($_POST['nama']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $email = trim($_POST['email']);

    $foto_profil = null;
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['foto_profil']['name']);
        $file_tmp = $_FILES['foto_profil']['tmp_name'];
        $upload_dir = "../uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        move_uploaded_file($file_tmp, $upload_dir . $file_name);
        $foto_profil = $file_name;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO anggota (username, nama, password, email, foto_profil) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $nama, $password, $email, $foto_profil]);
        header("Location: data-anggota_list.php");
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
    <title>Tambah Anggota</title>
    <link rel="stylesheet" href="data-anggota_create.css">
</head>

<body>
    <div class="container">
        <h1><i class="fas fa-user-plus"></i> Tambah Anggota</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="foto_profil">Foto Profil</label>
                <div class="file-input-container">
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*">
                    <span class="file-custom">Pilih File...</span>
                </div>
                <small class="text-muted">Biarkan kosong jika tidak ingin mengupload foto.</small>
            </div>
            <div class="button-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="data-anggota_list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</body>

</html>