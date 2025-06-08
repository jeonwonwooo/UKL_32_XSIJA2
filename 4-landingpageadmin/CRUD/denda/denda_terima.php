<?php
session_start();
include 'formkoneksi.php';

// Pastikan hanya admin yang bisa akses
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

    // Ambil data denda beserta peminjaman_id
    $stmt = $conn->prepare("
        SELECT d.peminjaman_id, d.anggota_id, p.status 
        FROM denda d
        JOIN peminjaman p ON d.peminjaman_id = p.id
        WHERE d.id = ?
    ");
    $stmt->execute([$denda_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception("Data denda tidak ditemukan.");
    }

    $peminjaman_id = $data['peminjaman_id'];
    $anggota_id = $data['anggota_id'];

    // Update status_pembayaran menjadi success
    $updateDenda = $conn->prepare("
        UPDATE denda 
        SET status_pembayaran = 'success', status = 'sudah_dibayar' 
        WHERE id = ?
    ");
    $updateDenda->execute([$denda_id]);

    // Reset jumlah_pengajuan ke 3 karena denda sudah dibayar
    $updatePengajuan = $conn->prepare("
        UPDATE peminjaman 
        SET jumlah_pengajuan = 3, status = 'dikembalikan' 
        WHERE id = ?
    ");
    $updatePengajuan->execute([$peminjaman_id]);

    // Ambil buku_id dari tabel peminjaman
    $stmt_buku = $conn->prepare("SELECT buku_id FROM peminjaman WHERE id = ?");
    $stmt_buku->execute([$peminjaman_id]);
    $buku_data = $stmt_buku->fetch(PDO::FETCH_ASSOC);

    if (!$buku_data) {
        throw new Exception("Data buku tidak ditemukan.");
    }

    $buku_id = $buku_data['buku_id'];

    $update_buku_query = "UPDATE buku SET status = 'tersedia' WHERE id = ?";
    $stmt_update_buku = $conn->prepare($update_buku_query);
    $stmt_update_buku->bindValue(1, $buku_id, PDO::PARAM_INT);
    $stmt_update_buku->execute();

    $conn->commit();

    // Redirect ke halaman aktivitas dengan notifikasi sukses
    header("Location: /CODINGAN/4-landingpageadmin/CRUD/denda/denda_list.php?status=denda_diterima&id={$denda_id}");
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