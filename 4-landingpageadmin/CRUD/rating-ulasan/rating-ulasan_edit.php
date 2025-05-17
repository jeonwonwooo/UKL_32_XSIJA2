<?php
include 'formkoneksi.php';

// Get rating ID from URL
$id = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT r.id, r.anggota_id, r.buku_id, r.nilai, r.ulasan, r.foto 
                        FROM rating r 
                        WHERE r.id = ?");
$stmt->execute([$id]);
$rating = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rating) {
    die("Rating tidak ditemukan.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anggota_id = $_POST['anggota_id'] ?? '';
    $buku_id = $_POST['buku_id'] ?? '';
    $nilai = $_POST['nilai'] ?? '';
    $ulasan = $_POST['ulasan'] ?? '';
    $foto = $_POST['foto'] ?? '';

    if (empty($anggota_id) || empty($buku_id) || empty($nilai)) {
        die("Semua field harus diisi.");
    }

    try {
        // Update rating
        $stmt = $conn->prepare("UPDATE rating SET anggota_id = ?, buku_id = ?, nilai = ?, ulasan = ?, foto = ? WHERE id = ?");
        $stmt->execute([$anggota_id, $buku_id, $nilai, $ulasan, $foto, $id]);

        header("Location: rating_list.php");
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
    <title>Edit Rating</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-star"></i> Edit Rating</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="anggota_id">Pengguna</label>
                <select name="anggota_id" id="anggota_id" required>
                    <option value="">Pilih Pengguna</option>
                    <?php
                    $stmt = $conn->query("SELECT id, username FROM anggota");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . htmlspecialchars($row['id']) . '"' . ($row['id'] == $rating['anggota_id'] ? ' selected' : '') . '>' . htmlspecialchars($row['username']) . '</option>';
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
                        echo '<option value="' . htmlspecialchars($row['id']) . '"' . ($row['id'] == $rating['buku_id'] ? ' selected' : '') . '>' . htmlspecialchars($row['judul']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="nilai">Nilai</label>
                <input type="number" name="nilai" id="nilai" value="<?= htmlspecialchars($rating['nilai']) ?>" min="1" max="5" required>
            </div>
            <div class="form-group">
                <label for="ulasan">Ulasan</label>
                <textarea name="ulasan" id="ulasan" rows="4"><?= htmlspecialchars($rating['ulasan']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="foto">Foto</label>
                <input type="text" name="foto" id="foto" value="<?= htmlspecialchars($rating['foto']) ?>">
            </div>
            <div class="button-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                <a href="rating_list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</body>
</html>