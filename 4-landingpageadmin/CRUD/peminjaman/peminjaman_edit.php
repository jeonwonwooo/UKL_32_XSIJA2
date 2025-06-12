<?php
include 'formkoneksi.php';
// Ambil ID dari URL
$peminjaman_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$peminjaman_id) {
    die("ID peminjaman tidak valid.");
}

// Query untuk mengambil data peminjaman (inner join dengan anggota dan buku)
$query = "
    SELECT 
        p.id, p.anggota_id, p.buku_id, p.tanggal_pinjam, p.batas_pengembalian,
        p.status,
        a.username, b.judul, b.tipe_buku
    FROM `peminjaman` p
    JOIN `anggota` a ON p.anggota_id = a.id
    JOIN `buku` b ON p.buku_id = b.id
    WHERE p.id = ?
";
$stmt = $conn->prepare($query);
$stmt->execute([$peminjaman_id]);
$peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$peminjaman) {
    die("Data peminjaman tidak ditemukan.");
}

// Variabel awal
$anggota_id = $peminjaman['anggota_id'];
$buku_id = $peminjaman['buku_id'];
$tanggal_pinjam = $peminjaman['tanggal_pinjam'];
$batas_pengembalian = $peminjaman['batas_pengembalian'];

// Ambil list anggota
$anggota_list = $conn->query("SELECT id, username FROM `anggota` ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

// Ambil list buku fisik
$buku_list = $conn->query("SELECT id, judul FROM `buku` WHERE tipe_buku = 'Buku Fisik' ORDER BY judul")->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anggota_id = intval($_POST['anggota_id']);
    $buku_id = intval($_POST['buku_id']);
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $batas_pengembalian = $_POST['batas_pengembalian'];

    // Validasi
    if ($anggota_id && $buku_id && $tanggal_pinjam && $batas_pengembalian) {
        try {
            $stmt_update = $conn->prepare("
                UPDATE `peminjaman`
                SET anggota_id = ?, buku_id = ?, tanggal_pinjam = ?, 
                    batas_pengembalian = ?
                WHERE id = ?
            ");
            $stmt_update->execute([
                $anggota_id,
                $buku_id,
                $tanggal_pinjam,
                $batas_pengembalian,
                $peminjaman_id
            ]);

            $success = "✅ Data peminjaman berhasil diperbarui.";
        } catch (PDOException $e) {
            $error = "❌ Gagal memperbarui data: " . $e->getMessage();
        }
    } else {
        $error = "⚠️ Semua field wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Peminjaman</title>
    <link rel="stylesheet" href="peminjaman_edit.css">
</head>
<body>
<div class="container">
    <h2>Edit Data Peminjaman</h2>

    <?php if ($error): ?>
        <div style="color: red; margin-bottom: 10px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="color: green; margin-bottom: 10px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="anggota_id">Pilih Anggota:</label>
        <select name="anggota_id" id="anggota_id" required>
            <option value="">-- Pilih Username --</option>
            <?php foreach ($anggota_list as $a): ?>
                <option value="<?= $a['id'] ?>" <?= ($a['id'] == $anggota_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['username']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="buku_id">Pilih Buku Fisik:</label>
        <select name="buku_id" id="buku_id" required>
            <option value="">-- Pilih Buku --</option>
            <?php foreach ($buku_list as $b): ?>
                <option value="<?= $b['id'] ?>" <?= ($b['id'] == $buku_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b['judul']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="tanggal_pinjam">Tanggal Pinjam:</label>
        <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" value="<?= htmlspecialchars($tanggal_pinjam) ?>" required>

        <label for="batas_pengembalian">Batas Pengembalian:</label>
        <input type="date" name="batas_pengembalian" id="batas_pengembalian" value="<?= htmlspecialchars($batas_pengembalian) ?>" required>

        <button type="submit">Simpan Perubahan</button>
        <a href="peminjaman_list.php" class="back-link">↩ Kembali</a>
    </form>
</div>
</body>
</html>