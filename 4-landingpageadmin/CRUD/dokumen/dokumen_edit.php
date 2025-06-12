<?php
include 'formkoneksi.php';

// Ambil ID dokumen dari URL
$id = $_GET['id'] ?? '';
$dokumen = null;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM dokumen WHERE id = ?");
    $stmt->execute([$id]);
    $dokumen = $stmt->fetch();
    if (!$dokumen) {
        die("Dokumen tidak ditemukan.");
    }
} else {
    die("ID Dokumen tidak valid.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $tahun_terbit = $_POST['tahun_terbit'];
    $tipe_dokumen = $_POST['tipe_dokumen'];
    $deskripsi = trim($_POST['deskripsi']);
    $status = $_POST['status'];

    $upload_dir = "../../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Upload file baru jika ada
    $file_path = $dokumen['file_path']; // Gunakan file lama sebagai default
    if (!empty($_FILES['file_path']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['file_path']['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'pdf') {
            die("Error: File harus dalam format PDF.");
        }

        $file_name = uniqid() . '_' . basename($_FILES['file_path']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['file_path']['tmp_name'], $target_file)) {
            // Hapus file lama jika berbeda
            if (!empty($dokumen['file_path']) && file_exists("../../" . $dokumen['file_path'])) {
                unlink("../../" . $dokumen['file_path']);
            }
            $file_path = "uploads/" . $file_name;
        } else {
            die("Error: Gagal mengupload file dokumen.");
        }
    }

    try {
        // Update data dokumen
        $stmt = $conn->prepare("UPDATE dokumen 
                                SET judul = ?, penulis = ?, tahun_terbit = ?, tipe_dokumen = ?, deskripsi = ?, status = ?, file_path = ?
                                WHERE id = ?");
        $stmt->execute([
            $judul,
            $penulis,
            $tahun_terbit,
            $tipe_dokumen,
            $deskripsi,
            $status,
            $file_path,
            $id
        ]);

        header("Location: dokumen_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Dokumen</title>
    <link rel="stylesheet" href="dokumen_edit.css">
</head>
<body>
<div class="container mt-5">
    <h1>Edit Dokumen</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="judul">Judul</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($dokumen['judul']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="penulis">Penulis</label>
            <input type="text" name="penulis" value="<?= htmlspecialchars($dokumen['penulis']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="tahun_terbit">Tahun Terbit</label>
            <input type="number" name="tahun_terbit" value="<?= htmlspecialchars($dokumen['tahun_terbit']) ?>" min="1900" max="2100">
        </div>

        <div class="mb-3">
            <label for="tipe_dokumen">Tipe Dokumen</label>
            <select name="tipe_dokumen" required>
                <?php
                $tipe_options = ['modul', 'karya_murid', 'tugas_akhir', 'makalah', 'laporan', 'jurnal', 'artikel_konferensi', 'lainnya'];
                foreach ($tipe_options as $tipe): ?>
                    <option value="<?= htmlspecialchars($tipe) ?>" <?= ($tipe === $dokumen['tipe_dokumen']) ? 'selected' : '' ?>>
                        <?= ucfirst(str_replace('_', ' ', $tipe)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="deskripsi">Deskripsi</label>
            <textarea name="deskripsi" rows="5"><?= htmlspecialchars($dokumen['deskripsi']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="file_path">Ganti File Dokumen (PDF)</label>
            <input type="file" name="file_path" accept=".pdf">
            <?php if (!empty($dokumen['file_path'])): ?>
                <p>File saat ini: <a href="../../<?= htmlspecialchars($dokumen['file_path']) ?>" target="_blank">Lihat File</a></p>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="status">Status</label>
            <select name="status" required>
                <option value="tersedia" <?= $dokumen['status'] === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                <option value="tidak tersedia" <?= $dokumen['status'] === 'tidak tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="dokumen_list.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>