<?php
include 'formkoneksi.php';

$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM anggota WHERE id = ?");
$stmt->execute([$id]);
$anggota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anggota) {
    die("Anggota tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $nama = trim($_POST['nama']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $password = $anggota['password'];
    }

    $foto_profil = $anggota['foto_profil'];
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['foto_profil']['name']);
        $file_tmp = $_FILES['foto_profil']['tmp_name'];
        $upload_dir = "../../uploads/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        move_uploaded_file($file_tmp, $upload_dir . $file_name);
        $foto_profil = $file_name;
    }

    try {
        $stmt = $conn->prepare("UPDATE anggota SET username = ?, nama = ?, password = ?, email = ?, foto_profil = ? WHERE id = ?");
        $stmt->execute([$username, $nama, $password, $email, $foto_profil, $id]);

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
    <title>Edit Anggota</title>
    <link rel="stylesheet" href="data-anggota_edit.css">
</head>

<body>
    <div class="container">
        <h1><i class="fas fa-user-edit"></i> Edit Anggota</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($anggota['username']) ?>" required>
            </div>
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($anggota['nama']) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($anggota['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="foto_profil">Foto Profil</label>
                <div class="file-input-container">
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*">
                    <span class="file-custom">Pilih File...</span>
                </div>
                <?php if ($anggota['foto_profil']): ?>
                    <div style="margin-top: 10px;">
                        <img src="../../uploads/<?= htmlspecialchars($anggota['foto_profil']) ?>" alt="Foto Profil" width="50">
                        <span style="color: #fff; font-size: 14px;">Foto Saat Ini</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="button-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                <a href="data-anggota_list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</body>

</html>