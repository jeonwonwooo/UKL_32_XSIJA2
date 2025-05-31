<?php
require_once 'formkoneksi.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: /CODINGAN/3-landingpageuser/login/login.php");
    exit();
}

$anggota_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

try {
    // Query statistik utama
    $query_stats = "
        SELECT 'total_buku' as stat_name, COUNT(DISTINCT buku_id) as stat_value FROM peminjaman WHERE anggota_id = ?
        UNION ALL
        SELECT 'peminjaman_aktif', COUNT(*) FROM peminjaman WHERE status = 'dipinjam' AND anggota_id = ?
        UNION ALL
        SELECT 'peminjaman_hari_ini', COUNT(*) FROM peminjaman WHERE tanggal_pinjam = ? AND anggota_id = ?
        UNION ALL
        SELECT 'pengembalian_hari_ini', COUNT(*) FROM peminjaman WHERE status = 'dikembalikan' AND tanggal_kembali = ? AND anggota_id = ?
        UNION ALL
        SELECT 'ebook_dipinjam', COUNT(*) FROM peminjaman WHERE tipe_buku = 'Buku Elektronik' AND status = 'dipinjam' AND anggota_id = ?
        UNION ALL
        SELECT 'total_denda', COUNT(*) FROM denda WHERE anggota_id = ? AND status = 'belum_dibayar'
    ";
    $stmt_stats = $conn->prepare($query_stats);
    $stmt_stats->execute([
        $anggota_id,
        $anggota_id,
        $current_date,
        $anggota_id,
        $current_date,
        $anggota_id,
        $anggota_id,
        $anggota_id
    ]);
    $stats = [];
    while ($row = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
        $stats[$row['stat_name']] = $row['stat_value'];
    }

    // Aktivitas Terbaru
    $query_activities = "
        SELECT 
            p.id AS peminjaman_id,
            b.judul AS judul_buku,
            p.tanggal_pinjam,
            p.status,
            p.status_pengajuan,
            p.jumlah_pengajuan,
            d.id AS denda_id,
            d.nominal AS denda_nominal,
            d.status AS denda_status,
            d.status_pembayaran AS denda_pembayaran,
            d.metode_pembayaran,
            d.tanggal_pembayaran,
            d.keterangan
        FROM peminjaman p
        JOIN buku b ON p.buku_id = b.id
        LEFT JOIN denda d ON p.id = d.peminjaman_id AND d.anggota_id = p.anggota_id
        WHERE p.anggota_id = ?
        ORDER BY p.id DESC
        LIMIT 5
    ";
    $stmt_activities = $conn->prepare($query_activities);
    $stmt_activities->execute([$anggota_id]);
    $activities = $stmt_activities->fetchAll(PDO::FETCH_ASSOC);

    // Notifikasi denda terbaru
    $query_denda_notif = "
        SELECT status_pembayaran, metode_pembayaran, tanggal_pembayaran
        FROM denda
        WHERE anggota_id = ?
        ORDER BY tanggal_denda DESC
        LIMIT 1
    ";
    $stmt_denda_notif = $conn->prepare($query_denda_notif);
    $stmt_denda_notif->execute([$anggota_id]);
    $denda_notif = $stmt_denda_notif->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Terjadi kesalahan pada database: " . htmlspecialchars($e->getMessage()));
}

// Notifikasi berdasarkan parameter URL
$url_notification = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'pengajuan_ditolak':
            $url_notification = '<div class="error-notification">Pengajuan diindikasikan penipuan. Batas maksimal pengajuan (3x) telah tercapai. Denda Rp50.000 dikenakan.</div>';
            break;
        case 'pengajuan_berhasil':
            $url_notification = '<div class="success-notification">Pengajuan pengembalian berhasil diajukan. Silakan tunggu verifikasi admin.</div>';
            break;
    }
}

// Fungsi hitung denda interval
function hitungDenda($dendaAwal, $jumlahPenolakan)
{
    $interval = 3;
    $peningkatan = 20000;
    $tambahanInterval = floor(($jumlahPenolakan - 1) / $interval);
    return $dendaAwal + $tambahanInterval * $peningkatan;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivitas Peminjaman</title>
    <link rel="stylesheet" href="aktivitas.css">
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
        }

        .success-notification {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }

        .error-notification {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }

        .warning-notification {
            background-color: #fff3cd;
            color: #856404;
            border-left: 5px solid #ffc107;
        }

        .info-notification {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 5px solid #17a2b8;
        }

        .notification i {
            margin-right: 10px;
            font-size: 1.5em;
        }

        .notification-content {
            flex: 1;
        }

        .notification p {
            margin: 5px 0 0 0;
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo"><img src="logo.png" alt="Logo Perpus"></div>
        <nav class="navbar">
            <ul>
                <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/profil/umum/profil.php">Tentang</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/layanan/layanan.php">Layanan</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.php">Kontak</a></li>
                <li class="profil"><a href="/CODINGAN/3-landingpageuser/akun/akun.php" class="akun"><i
                            class="fas fa-user"></i></a></li>
                <li><button class="btn-logout"><i class="fas fa-arrow-left"></i> <a
                            href="/CODINGAN/3-landingpageuser/layanan/layanan.php">Kembali</a></button></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="intro-content">
            <h3>Aktivitas Anda</h3>
            <p>Statistik peminjaman dan pengembalian buku Anda di perpustakaan kami.</p>
        </div>
        <div class="isi">
            <h3>Statistik Utama</h3>
            <div class="statistik-wrapper">
                <div class="text1">
                    <p>Statistik ini akan diperbarui secara <i>real-time</i>. Berisikan riwayat aktivitas secara
                        keseluruhan di perpustakaan kami.</p>
                </div>
                <div class="statistik">
                    <table class="stats-table">
                        <tbody>
                            <tr>
                                <td>Total Buku Dipinjam</td>
                                <td><?= htmlspecialchars($stats['total_buku'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <td>Peminjaman Aktif</td>
                                <td><?= htmlspecialchars($stats['peminjaman_aktif'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <td>Peminjaman Hari Ini</td>
                                <td><?= htmlspecialchars($stats['peminjaman_hari_ini'] ?? 0) ?></td>
                            </tr>
                            <tr>
                                <td>Total Denda Belum Dibayar</td>
                                <td><?= htmlspecialchars($stats['total_denda'] ?? 0) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div id="activity-table" class="aktivitas">
            <?= $url_notification ?>
            <!-- Notifikasi Utama -->
            <?php if (!empty($activities)): ?>
                <?php
                $latest_activity = $activities[0];
                $sisa_kesempatan = max(0, $latest_activity['jumlah_pengajuan'] ?? 3);
                $status_pengajuan = $latest_activity['status_pengajuan'] ?? null;
                $status = $latest_activity['status'] ?? null;
                $denda_id = $latest_activity['denda_id'] ?? null;
                $keterangan = $latest_activity['keterangan'] ?? '';
                $denda_nominal = $latest_activity['denda_nominal'] ?? 50000;
                // Hitung jumlah penolakan dari keterangan
                preg_match_all('/Pembayaran ditolak oleh admin/', $keterangan, $matches);
                $jumlahPenolakan = count($matches[0]) + 1;
                $nominalFinal = hitungDenda($denda_nominal, $jumlahPenolakan);
                $interval = 3;
                $kenaikan = '';
                if (($jumlahPenolakan - 1) % $interval == 0 && $jumlahPenolakan > 1) {
                    $kenaikan = '<br><b>PERINGATAN:</b> Denda naik menjadi Rp' . number_format($nominalFinal, 0, ',', '.') . ' karena pengajuan ditolak ' . $jumlahPenolakan . ' kali.';
                }
                ?>
                <?php if ($status_pengajuan === 'diterima'): ?>
                    <div class="notification success-notification">
                        <i class="fas fa-check-circle"></i>
                        <div class="notification-content">
                            <span>Pengajuan pengembalian Anda telah diterima.</span>
                        </div>
                    </div>
               <?php elseif ($status_pengajuan === 'ditolak'): ?>
    <?php if ($sisa_kesempatan <= 0 && !empty($denda_id)): ?>
        <div class="notification error-notification">
            <i class="fas fa-exclamation-circle"></i>
            <div class="notification-content">
                <span>Pengajuan diindikasikan penipuan. Batas maksimal pengajuan (3x) telah tercapai.</span>
                <p>
                    Denda Rp<?= number_format($denda_nominal, 0, ',', '.') ?> dikenakan (mengikuti nominal denda terakhir di sistem).
                </p>
            </div>
        </div>
    <?php elseif ($latest_activity['denda_pembayaran'] === 'success' || $latest_activity['denda_pembayaran'] === 'sudah_dibayar'): ?>
        <div class="notification success-notification">
            <i class="fas fa-check-circle"></i>
            <div class="notification-content">
                <span>Denda sudah dibayar. Terima kasih telah jujur!</span>
            </div>
    <?php else: ?>
        <div class="notification warning-notification">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="notification-content">
                <span>Pengajuan ditolak karena tidak valid. Sisa kesempatan pengajuan: <?= $sisa_kesempatan ?></span>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
            <?php else: ?>
                <div class="notification info-notification">
                    <i class="fas fa-info-circle"></i>
                    <div class="notification-content">
                        <span>Belum ada riwayat aktivitas peminjaman.</span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabel Aktivitas -->
            <h3>Aktivitas Terbaru</h3>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Status</th>
                        <th>Status Pengajuan</th>
                        <th>Sisa Kesempatan</th>
                        <th>Denda</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $index => $activity): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($activity['judul_buku']) ?></td>
                            <td><?= htmlspecialchars($activity['tanggal_pinjam']) ?></td>
                            <td><?= htmlspecialchars($activity['status']) ?></td>
                            <td><?= htmlspecialchars($activity['status_pengajuan'] ?? '-') ?></td>
                            <td><?= $activity['status'] === 'dipinjam' ? $activity['jumlah_pengajuan'] : '-' ?></td>
                            <td>
                                <?php if (!empty($activity['denda_id'])): ?>
                                    Rp<?= number_format($activity['denda_nominal'], 0, ',', '.') ?>
                                    (<?= htmlspecialchars($activity['denda_status']) ?>)
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $denda_status = $activity['denda_status'] ?? null;
                                $denda_pembayaran = $activity['denda_pembayaran'] ?? null;
                                $denda_id = $activity['denda_id'] ?? null;
                                $status = $activity['status'] ?? null;
                                $status_pengajuan = $activity['status_pengajuan'] ?? null;
                                $sisa_kesempatan = max(0, $activity['jumlah_pengajuan'] ?? 3);
                                $peminjaman_id = $activity['peminjaman_id'] ?? null;

                                if ($status === 'dikembalikan') {
                                    echo '<span class="status-pengajuan-diterima">Buku telah dikembalikan</span>';
                                } elseif (!empty($denda_id)) {
                                    // Jika status denda masih proses, tampilkan "Denda Diproses"
                                    if ($denda_status === 'proses') {
        echo '<span class="btn-action disabled">Denda Diproses</span>';
                                        // Jika pembayaran gagal atau belum dibayar, tampilkan tombol bayar denda
                                    } elseif ($denda_pembayaran === 'belum_dibayar' || $denda_pembayaran === 'pending') {
        echo '<a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/denda/bayar_denda.php?id=' . htmlspecialchars($denda_id) . '" class="btn-action red">Bayar Denda</a>';
                                        // Jika pembayaran sedang diproses
                                    } elseif ($denda_pembayaran === 'failed' || $denda_pembayaran === 'belum_dibayar') {
        echo '<a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/denda/bayar_denda.php?id=' . htmlspecialchars($denda_id) . '" class="btn-action red">Bayar Lagi</a>';
                                        // Jika sudah dibayar
                                    } elseif ($denda_pembayaran === 'success' || $denda_pembayaran === 'sudah_dibayar') {
                                        echo '<span class="status-pengajuan-diterima">Denda Sudah Dibayar</span>';
                                    } else {
                                        echo '<span class="btn-action disabled">Status Denda Tidak Dikenali</span>';
                                    }
                                } elseif ($status === 'dipinjam' && $sisa_kesempatan > 0) {
                                    if ($status_pengajuan === 'ditolak') {
                                        echo '<a href="ajukan-kembali.php?id=' . htmlspecialchars($peminjaman_id) . '" class="btn-action">Ajukan Kembali</a>';
                                    } elseif ($status_pengajuan === 'menunggu') {
                                        echo '<span class="status-pengajuan-menunggu">Menunggu Verifikasi Admin</span>';
                                    } else {
                                        if ($sisa_kesempatan == 3) {
                                            echo '<a href="ajukan-kembali.php?id=' . htmlspecialchars($peminjaman_id) . '" class="btn-action">Ajukan Pengembalian</a>';
                                        } else {
                                            echo '<a href="ajukan-kembali.php?id=' . htmlspecialchars($peminjaman_id) . '" class="btn-action">Ajukan Kembali</a>';
                                        }
                                    }
                                } elseif ($status === 'dipinjam' && $sisa_kesempatan <= 0) {
                                    if ($status_pengajuan === 'menunggu') {
                                        echo '<span class="status-pengajuan-menunggu">Menunggu Verifikasi Admin</span>';
                                    } elseif ($status_pengajuan === 'ditolak') {
                                        echo '<span class="status-pengajuan-ditolak">Pengajuan Ditolak</span>';
                                    } else {
                                        echo '<span class="btn-action disabled">Tidak Ada Aksi</span>';
                                    }
                                } elseif ($status_pengajuan === 'menunggu') {
                                    echo '<span class="status-pengajuan-menunggu">Menunggu Verifikasi Admin</span>';
                                } elseif ($status_pengajuan === 'ditolak' && $sisa_kesempatan > 0) {
                                    echo '<span class="status-pengajuan-ditolak">Pengajuan Ditolak</span>';
                                } else {
                                    echo '<span class="btn-action disabled">Tidak Ada Aksi</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="laporan-kerusakan">
            <div class="left-column">
                <iframe
                    src="https://docs.google.com/forms/d/e/1FAIpQLSeeHmEzoQgW-ZkDoWk7NKS_2sXPFqMoPTHLIkQ-Dd5-kF7xwQ/viewform?embedded=true"
                    allowfullscreen loading="lazy"></iframe>
            </div>
            <div class="right-column">
                <h3>Laporan Kerusakan</h3>
                <p>
                    Jika Anda menemukan kerusakan pada buku atau fasilitas perpustakaan, silakan laporkan melalui
                    formulir di samping.
                    Kami akan memproses laporan Anda secepat mungkin untuk memastikan kenyamanan pengguna perpustakaan.
                </p>
            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="container">
            <div class="left">
                <img src="logo.png" alt="Library of Riverhill Senior High School logo" />
                <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Repudiandae omnis molestias nobis.</p>
                <div class="social-icons">
                    <a href="https://wa.me/6285936164597" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.linkedin.com/in/syarivatun-nisa-i-nur-aulia-3ab52b2bb/" target="_blank"><i
                            class="fab fa-linkedin"></i></a>
                    <a href="https://instagram.com/jeonwpnwoo" target="_blank"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="right">
                <h2>Tautan Fungsional</h2>
                <ul>
                    <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.html">Beranda</a></li>
                    <li><a href="/CODINGAN/3-landingpageuser/layanan/layanan.html">Layanan</a></li>
                    <li><a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            Copyright © 2024 Library of Riverhill Senior High School. All Rights Reserved
        </div>
    </footer>
</body>

</html>