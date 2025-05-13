<?php
include 'formkoneksi.php';

// Debugging: Pastikan $conn ada
if (!$conn) {
    die("Koneksi database tidak tersedia.");
}

// Inisialisasi variabel
$username = '';
$tanggal_pinjam = date('Y-m-d'); // Default hari ini
$batas_pengembalian = date('Y-m-d', strtotime('+7 days')); // Default 7 hari setelah pinjam
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
            $stmt_anggota->execute();
            $anggota = $stmt_anggota->fetch(PDO::FETCH_ASSOC);

            if (!$anggota) {
                $error = "Username tidak ditemukan.";
            } else {
                echo "Silakan pilih buku terlebih dahulu dari katalog.";
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
    <title>Form Peminjaman</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>Form Peminjaman</h1>

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
            <button type="submit" class="btn btn-primary">Simpan Peminjaman</button>
            <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/katalog/katalog.php" class="btn btn-secondary">Ke Katalog Buku</a>
        </form>
    </div>
</body>

</html>