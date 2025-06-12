<?php
include 'formkoneksi.php';

$tanggal_pinjam = date('Y-m-d');
$batas_pengembalian = date('Y-m-d', strtotime('+7 days'));
$error = '';
$success = '';
$buku_list = [];

// Ambil daftar buku fisik (langsung saat halaman dibuka)
try {
    $stmt_buku = $conn->query("SELECT id, judul FROM buku WHERE tipe_buku = 'Buku Fisik'");
    $buku_list = $stmt_buku->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Gagal mengambil daftar buku: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $batas_pengembalian = $_POST['batas_pengembalian'];
    $buku_id = intval($_POST['buku_id']);

    if (empty($username) || empty($tanggal_pinjam) || empty($batas_pengembalian) || $buku_id <= 0) {
        $error = "Semua kolom harus diisi.";
    } else {
        // Cek user
        $stmt_anggota = $conn->prepare("SELECT id FROM anggota WHERE username = ?");
        $stmt_anggota->execute([$username]);
        $anggota = $stmt_anggota->fetch(PDO::FETCH_ASSOC);

        if ($anggota) {
            $anggota_id = $anggota['id'];

            // Simpan peminjaman
            try {
                $stmt_insert = $conn->prepare("
                    INSERT INTO peminjaman (
                        anggota_id, buku_id, tanggal_pinjam, batas_pengembalian, status
                    ) VALUES (?, ?, ?, ?, 'dipinjam')
                ");
                $stmt_insert->execute([
                    $anggota_id, $buku_id, $tanggal_pinjam, $batas_pengembalian
                ]);
                $success = "Peminjaman berhasil disimpan!";
            } catch (PDOException $e) {
                $error = "Gagal menyimpan peminjaman: " . $e->getMessage();
            }
        } else {
            $error = "Username tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Peminjaman</title>
    <link rel="stylesheet" href="peminjaman_create.css">
</head>
<body>
    <h2>Form Peminjaman Buku Fisik</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="username">Username Anggota:</label>
        <input type="text" name="username" id="username" required>

        <label for="tanggal_pinjam">Tanggal Pinjam:</label>
        <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" value="<?= htmlspecialchars($tanggal_pinjam) ?>" required>

        <label for="batas_pengembalian">Batas Pengembalian:</label>
        <input type="date" name="batas_pengembalian" id="batas_pengembalian" value="<?= htmlspecialchars($batas_pengembalian) ?>" required>

        <label for="buku_id">Pilih Buku Fisik:</label>
        <select name="buku_id" id="buku_id" required>
            <option value="">-- Pilih Buku --</option>
            <?php foreach ($buku_list as $buku): ?>
                <option value="<?= htmlspecialchars($buku['id']) ?>">
                    <?= htmlspecialchars($buku['judul']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Simpan Peminjaman</button>
    </form>

</body>
</html>
