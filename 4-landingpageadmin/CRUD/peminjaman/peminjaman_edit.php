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
        p.status, p.denda_status,
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
$status = $peminjaman['status'];

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
    $status = $_POST['status'];

    // Validasi
    if ($anggota_id && $buku_id && $tanggal_pinjam && $batas_pengembalian) {
        try {
            // Update data peminjaman
            $stmt_update = $conn->prepare("
                UPDATE `peminjaman`
                SET anggota_id = ?, buku_id = ?, tanggal_pinjam = ?, 
                    batas_pengembalian = ?, status = ?
                WHERE id = ?
            ");
            $stmt_update->execute([
                $anggota_id,
                $buku_id,
                $tanggal_pinjam,
                $batas_pengembalian,
                $status,
                $peminjaman_id
            ]);

            // Hitung denda jika status adalah 'dipinjam' dan melewati batas pengembalian
            if ($status === 'dipinjam') {
                $today = date('Y-m-d'); // Tanggal saat ini
                if ($today > $batas_pengembalian) {
                    // Hitung jumlah hari terlambat
                    $diff = strtotime($today) - strtotime($batas_pengembalian);
                    $days_late = ceil($diff / (60 * 60 * 24));

                    // Hitung denda
                    $denda = 5000; // Denda hari pertama
                    if ($days_late > 1) {
                        $denda += ($days_late - 1) * 2000; // Denda tambahan setiap hari
                    }

                    // Simpan denda ke tabel `denda`
                    $stmt_denda = $conn->prepare("
                        INSERT INTO `denda` (
                            anggota_id, peminjaman_id, nominal, status, tanggal_denda
                        ) VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt_denda->execute([
                        $anggota_id,
                        $peminjaman_id,
                        $denda,
                        'belum_dibayar',
                        $today
                    ]);

                    // Perbarui status denda di tabel `peminjaman`
                    $stmt_update_denda_status = $conn->prepare("
                        UPDATE `peminjaman`
                        SET denda_status = ?
                        WHERE id = ?
                    ");
                    $stmt_update_denda_status->execute([
                        'belum_dibayar',
                        $peminjaman_id
                    ]);
                }
            }

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
        <div class="form-group">
            <label for="anggota_id">Pilih Anggota:</label>
            <select name="anggota_id" id="anggota_id" required>
                <option value="">-- Pilih Username --</option>
                <?php foreach ($anggota_list as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= ($a['id'] == $anggota_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="buku_id">Pilih Buku Fisik:</label>
            <select name="buku_id" id="buku_id" required>
                <option value="">-- Pilih Buku --</option>
                <?php foreach ($buku_list as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($b['id'] == $buku_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['judul']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="tanggal_pinjam">Tanggal Pinjam:</label>
            <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" value="<?= htmlspecialchars($tanggal_pinjam) ?>" required>
        </div>

        <div class="form-group">
            <label for="batas_pengembalian">Batas Pengembalian:</label>
            <input type="date" name="batas_pengembalian" id="batas_pengembalian" value="<?= htmlspecialchars($batas_pengembalian) ?>" required>
        </div>

        <div class="form-group">
            <label for="status">Status Peminjaman:</label>
            <select name="status" id="status" required>
                <option value="dipinjam" <?= $status === 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                <option value="dikembalikan" <?= $status === 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
            </select>
        </div>

        <button type="submit">Simpan Perubahan</button>
        <a href="peminjaman_list.php" class="back-link">↩ Kembali</a>
    </form>
</div>
</body>
</html>