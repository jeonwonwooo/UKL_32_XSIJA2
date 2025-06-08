<?php
include 'formkoneksi.php';

// Debugging: Pastikan $conn ada
if (!$conn) {
    die("Koneksi database tidak tersedia.");
}

// Ambil ID dari URL
$peminjaman_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

// Ambil data peminjaman beserta username anggota
$query = "
    SELECT p.id, a.username, p.tanggal_pinjam, p.batas_pengembalian
    FROM peminjaman p
    JOIN anggota a ON p.anggota_id = a.id
    WHERE p.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bindValue(1, $peminjaman_id, PDO::PARAM_INT);
$stmt->execute();
$peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$peminjaman) {
    die("Data peminjaman tidak ditemukan.");
}

// Inisialisasi variabel dengan data dari database
$username = $peminjaman['username'];
$tanggal_pinjam = $peminjaman['tanggal_pinjam'];
$batas_pengembalian = $peminjaman['batas_pengembalian'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $username = trim($_POST['username']);
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $batas_pengembalian = $_POST['batas_pengembalian'];

    // Validasi data
    if (empty($username) || empty($tanggal_pinjam) || empty($batas_pengembalian)) {
        $error = "Semua field wajib diisi.";
    } else {
        try {
            // Cari anggota_id berdasarkan username
            $anggota_query = "SELECT id FROM anggota WHERE username = ?";
            $stmt_anggota = $conn->prepare($anggota_query);
            $stmt_anggota->bindValue(1, $username, PDO::PARAM_STR);
            $stmt_anggota->execute();
            $anggota = $stmt_anggota->fetch(PDO::FETCH_ASSOC);

            if (!$anggota) {
                $error = "Username tidak ditemukan.";
            } else {
                // Update data peminjaman
                $update_query = "
                    UPDATE peminjaman
                    SET anggota_id = ?, tanggal_pinjam = ?, batas_pengembalian = ?
                    WHERE id = ?
                ";
                $stmt_update = $conn->prepare($update_query);
                $stmt_update->bindValue(1, $anggota['id'], PDO::PARAM_INT);
                $stmt_update->bindValue(2, $tanggal_pinjam, PDO::PARAM_STR);
                $stmt_update->bindValue(3, $batas_pengembalian, PDO::PARAM_STR);
                $stmt_update->bindValue(4, $peminjaman_id, PDO::PARAM_INT);

                if ($stmt_update->execute()) {
                    $success = "Data peminjaman berhasil diperbarui.";
                } else {
                    $error = "Gagal memperbarui data peminjaman.";
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Peminjaman</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Data Peminjaman</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username Anggota</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username..." value="<?= htmlspecialchars($username) ?>" required>
            </div>
            <div class="mb-3">
                <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam</label>
                <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam" value="<?= htmlspecialchars($tanggal_pinjam) ?>" required>
            </div>
            <div class="mb-3">
                <label for="batas_pengembalian" class="form-label">Batas Pengembalian</label>
                <input type="date" class="form-control" id="batas_pengembalian" name="batas_pengembalian" value="<?= htmlspecialchars($batas_pengembalian) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Perbarui Peminjaman</button>
            <a href="peminjaman_list.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>