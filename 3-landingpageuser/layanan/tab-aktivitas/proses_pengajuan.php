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

    $sisa_kesempatan = $peminjaman['jumlah_pengajuan'] - 1;
    $anggota_id = $peminjaman['anggota_id'];

    if ($sisa_kesempatan < 0) {
        // Jika kesempatan sudah habis (0 atau kurang), kenakan denda
        
        // Update status peminjaman
        $update = $conn->prepare("
            UPDATE peminjaman 
            SET status_pengajuan = 'ditolak', jumlah_pengajuan = 0 
            WHERE id = ?
        ");
        $update->execute([$peminjaman_id]);

        // Cek apakah denda sudah pernah dicatat
        $cek_denda = $conn->prepare("SELECT id FROM denda WHERE peminjaman_id = ?");
        $cek_denda->execute([$peminjaman_id]);
        
        if (!$cek_denda->fetch()) {
            // Tambahkan data denda baru
            $insert_denda = $conn->prepare("
                INSERT INTO denda (anggota_id, peminjaman_id, nominal, status, keterangan, created_at)
                VALUES (?, ?, 50000, 'belum_dibayar', 'Melebihi batas pengajuan', NOW())
            ");
            $insert_denda->execute([$anggota_id, $peminjaman_id]);
        }

        echo "Pengajuan ditolak karena melebihi batas maksimal. Denda Rp50.000 telah dikenakan.";
    } else {
        // Update status pengajuan menjadi 'menunggu' dan kurangi sisa kesempatan
        $update = $conn->prepare("
            UPDATE peminjaman 
            SET status_pengajuan = 'menunggu', jumlah_pengajuan = ?, pengajuan_pengembalian = NOW() 
            WHERE id = ?
        ");
        $update->execute([$sisa_kesempatan, $peminjaman_id]);

        echo "Pengajuan berhasil diajukan. Sisa kesempatan: " . $sisa_kesempatan . " kali lagi.";
    }

    // Commit transaksi
    $conn->commit();
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollBack();
    die("ERROR: " . $e->getMessage());
}
?>