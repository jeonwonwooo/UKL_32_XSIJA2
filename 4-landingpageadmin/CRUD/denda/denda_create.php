<?php
include 'formkoneksi.php';

// Debugging: Pastikan $conn ada
if (!$conn) {
    die("Koneksi database tidak tersedia.");
}

// Inisialisasi variabel
$anggota_id = '';
$peminjaman_id = '';
$nominal = '';
$status = 'belum_dibayar';
$tanggal_denda = date('Y-m-d H:i:s');
$keterangan = '';
$notifikasi_pembayaran = 0;
$bukti_pembayaran = '';
$status_pembayaran = 'pending';
$metode_pembayaran = '';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $anggota_id = trim($_POST['anggota_id']);
    $peminjaman_id = trim($_POST['peminjaman_id']);
    $nominal = trim($_POST['nominal']);
    $status = trim($_POST['status']);
    $keterangan = trim($_POST['keterangan']);
    $notifikasi_pembayaran = isset($_POST['notifikasi_pembayaran']) ? 1 : 0;
    $bukti_pembayaran = trim($_POST['bukti_pembayaran']);
    $status_pembayaran = trim($_POST['status_pembayaran']);
    $metode_pembayaran = trim($_POST['metode_pembayaran']);

    // Validasi data
    if (
        empty($anggota_id) || 
        empty($peminjaman_id) || 
        empty($nominal) || 
        !in_array($status, ['belum_dibayar', 'sudah_dibayar']) || 
        !in_array($status_pembayaran, ['pending', 'success', 'failed']) || 
        empty($metode_pembayaran)
    ) {
        $error = "Semua field wajib diisi.";
    } else {
        try {
            // Query untuk menyimpan data denda
            $query = "
                INSERT INTO perpus_denda (
                    anggota_id, peminjaman_id, nominal, status, tanggal_denda, keterangan, 
                    notifikasi_pembayaran, bukti_pembayaran, status_pembayaran, metode_pembayaran
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(1, $anggota_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $peminjaman_id, PDO::PARAM_INT);
            $stmt->bindValue(3, $nominal, PDO::PARAM_STR);
            $stmt->bindValue(4, $status, PDO::PARAM_STR);
            $stmt->bindValue(5, $tanggal_denda, PDO::PARAM_STR);
            $stmt->bindValue(6, $keterangan, PDO::PARAM_STR);
            $stmt->bindValue(7, $notifikasi_pembayaran, PDO::PARAM_INT);
            $stmt->bindValue(8, $bukti_pembayaran, PDO::PARAM_STR);
            $stmt->bindValue(9, $status_pembayaran, PDO::PARAM_STR);
            $stmt->bindValue(10, $metode_pembayaran, PDO::PARAM_STR);

            // Eksekusi query
            if ($stmt->execute()) {
                $success = "Data denda berhasil ditambahkan.";
            } else {
                $error = "Gagal menyimpan data denda.";
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
    <title>Tambah Denda</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Tambah Data Denda</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="mb-3">
                <label for="anggota_id" class="form-label">ID Anggota</label>
                <input type="text" class="form-control" id="anggota_id" name="anggota_id" placeholder="Masukkan ID Anggota..." required>
            </div>
            <div class="mb-3">
                <label for="peminjaman_id" class="form-label">ID Peminjaman</label>
                <input type="text" class="form-control" id="peminjaman_id" name="peminjaman_id" placeholder="Masukkan ID Peminjaman..." required>
            </div>
            <div class="mb-3">
                <label for="nominal" class="form-label">Nominal Denda</label>
                <input type="number" step="0.01" class="form-control" id="nominal" name="nominal" placeholder="Masukkan nominal denda..." required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status Denda</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="belum_dibayar">Belum Dibayar</option>
                    <option value="sudah_dibayar">Sudah Dibayar</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="notifikasi_pembayaran" class="form-label">Notifikasi Pembayaran</label>
                <input type="checkbox" id="notifikasi_pembayaran" name="notifikasi_pembayaran">
            </div>
            <div class="mb-3">
                <label for="bukti_pembayaran" class="form-label">Bukti Pembayaran</label>
                <input type="text" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" placeholder="Masukkan bukti pembayaran...">
            </div>
            <div class="mb-3">
                <label for="status_pembayaran" class="form-label">Status Pembayaran</label>
                <select class="form-control" id="status_pembayaran" name="status_pembayaran" required>
                    <option value="pending">Pending</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                <select class="form-control" id="metode_pembayaran" name="metode_pembayaran" required>
                    <option value="BCA">BCA</option>
                    <option value="Mandiri">Mandiri</option>
                    <option value="BNI">BNI</option>
                    <option value="BRI">BRI</option>
                    <option value="QRIS">QRIS</option>
                    <option value="ShopeePay">ShopeePay</option>
                    <option value="Dana">Dana</option>
                    <option value="GoPay">GoPay</option>
                    <option value="OVO">OVO</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Denda</button>
            <a href="denda_list.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>