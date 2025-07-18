<?php
session_start();
include 'formkoneksi.php';

// Ambil ID peminjaman dari GET
$peminjaman_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$peminjaman_id) {
    die("ID PEMINJAMAN TIDAK VALID");
}

try {
    // Mulai transaksi
    $conn->beginTransaction();

    // Ambil data peminjaman termasuk jumlah_pengajuan dan anggota_id
    $query = "SELECT jumlah_pengajuan, anggota_id FROM peminjaman WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$peminjaman_id]);
    $peminjaman = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$peminjaman) {
        throw new Exception("Data peminjaman tidak ditemukan.");
    }

    $jumlah_pengajuan = $peminjaman['jumlah_pengajuan'];
    $anggota_id = $peminjaman['anggota_id'];

    // Update status pengajuan menjadi 'ditolak'
    $update = $conn->prepare("
        UPDATE peminjaman 
        SET status_pengajuan = 'ditolak' 
        WHERE id = ?
    ");
    $update->execute([$peminjaman_id]);

    // Jika jumlah pengajuan <= 0 (sudah melebihi batas), kenakan denda
    if ($jumlah_pengajuan <= 0) {
        // Cek apakah denda sudah pernah dicatat
        $cek_denda = $conn->prepare("SELECT id FROM denda WHERE peminjaman_id = ?");
        $cek_denda->execute([$peminjaman_id]);
        
        if (!$cek_denda->fetch()) {
            // Tambahkan data denda baru
            $insert_denda = $conn->prepare("
                INSERT INTO denda (anggota_id, peminjaman_id, nominal, status, keterangan, created_at)
                VALUES (?, ?, 50000, 'belum_dibayar', 'Terindikasi penipuan karena melebihi batas pengajuan pengembalian buku namun buku tidak ada di koleksi perpustakaan.', NOW())
            ");
            $insert_denda->execute([$anggota_id, $peminjaman_id]);
            
            // Update status denda di tabel peminjaman
            $update_denda = $conn->prepare("
                UPDATE peminjaman 
                SET denda_status = 'belum_dibayar' 
                WHERE id = ?
            ");
            $update_denda->execute([$peminjaman_id]);
        }
    }

    // Commit transaksi
    $conn->commit();

    // Redirect admin kembali ke halaman daftar peminjaman
    header("Location: peminjaman_list.php?status=pengajuan_ditolak");
    exit;
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollBack();
    die("ERROR: " . $e->getMessage());
}
?>