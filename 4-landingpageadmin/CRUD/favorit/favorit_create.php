<?php
include 'formkoneksi.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $buku_id = $_POST['buku_id'] ?? '';

    if (empty($user_id) || empty($buku_id)) {
        die("Semua field harus diisi.");
    }

    try {
        // Insert new favorite entry
        $stmt = $conn->prepare("INSERT INTO favorit (user_id, buku_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $buku_id]);

        header("Location: favorit_list.php");
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
    <title>Tambah Favorit</title>
    <link rel="stylesheet" href="favorit_create.css">
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-heart"></i> Tambah Favorit</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="user_id">Pengguna</label>
                <select name="user_id" id="user_id" required>
                    <option value="">Pilih Pengguna</option>
                    <?php
                    $stmt = $conn->query("SELECT id, username FROM anggota");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['username']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="buku_id">Buku</label>
                <select name="buku_id" id="buku_id" required>
                    <option value="">Pilih Buku</option>
                    <?php
                    $stmt = $conn->query("SELECT id, judul FROM buku");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['judul']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="button-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="favorit_list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</body>
</html>