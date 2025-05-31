<?php
session_start();
include 'formkoneksi.php';

// Hanya admin yang bisa akses
if (!isset($_SESSION['admin_id'])) {
    die("Akses ditolak. Harus login sebagai admin.");
}

// Ambil ID denda dari URL
$denda_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$denda_id) {
    die("ID DENDA TIDAK VALID");
}

try {
    $conn->beginTransaction();

    // Ambil data denda
    $stmt = $conn->prepare("SELECT * FROM denda WHERE id = ?");
    $stmt->execute([$denda_id]);
    $denda = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$denda) {
        throw new Exception("Data denda tidak ditemukan.");
    }

    // Hitung jumlah penolakan dari keterangan
    $keterangan = $denda['keterangan'] ?? '';
    preg_match_all('/Pembayaran ditolak oleh admin/', $keterangan, $matches);
    $jumlah_penolakan = count($matches[0]) + 1; // +1 karena penolakan sekarang

    // Hitung denda berdasarkan kelipatan 3 penolakan
    function hitungDenda($denda_awal, $jumlah_penolakan) {
        $interval = 3;
        $kenaikan = 20000;
        $tambahan_interval = floor(($jumlah_penolakan - 1) / $interval);
        return $denda_awal + ($tambahan_interval * $kenaikan);
    }

    $nominal_baru = hitungDenda($denda['nominal'], $jumlah_penolakan);

    // Update status pembayaran dan tambahkan riwayat penolakan
    $update = $conn->prepare("UPDATE denda SET 
        status_pembayaran = 'failed',
        status = 'belum_dibayar',
        nominal = ?,
        keterangan = CONCAT(IFNULL(keterangan, ''), '\n- Pembayaran ditolak oleh admin pada ', NOW()) 
        WHERE id = ?");
    $update->execute([$nominal_baru, $denda_id]);

    $conn->commit();

    // Redirect ke halaman admin
    header("Location: denda_list.php?status=ditolak&id={$denda_id}");
    exit();

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    die("Database Error: " . htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>