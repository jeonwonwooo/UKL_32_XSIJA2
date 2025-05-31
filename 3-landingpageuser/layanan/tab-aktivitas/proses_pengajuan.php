<?php
include 'formkoneksi.php';

// Ambil ID peminjaman dari GET
$peminjaman_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

try {
    // Mulai transaksi
    $conn->beginTransaction();

    // Ambil data peminjaman termasuk anggota_id
    $query = "SELECT jumlah_pengajuan, anggota_id FROM peminjaman WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$peminjaman_id]);
    $peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$peminjaman) {
        throw new Exception("Data peminjaman tidak ditemukan.");
    }

    $jumlah_pengajuan = max(0, ($peminjaman['jumlah_pengajuan'] ?? 3));
    $anggota_id = $peminjaman['anggota_id'];

    // Kurangi jumlah_pengajuan
    $sisa_pengajuan = $jumlah_pengajuan - 1;

    // Update status_pengajuan dan jumlah_pengajuan
    $update_pengajuan = $conn->prepare("UPDATE peminjaman SET status_pengajuan = 'menunggu', jumlah_pengajuan = ? WHERE id = ?");
    $update_pengajuan->execute([$sisa_pengajuan, $peminjaman_id]);

    $conn->commit();
    header("Location: /CODINGAN/3-landingpageuser/layanan/tab-aktivitas/aktivitas.php?status=pengajuan_berhasil");
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