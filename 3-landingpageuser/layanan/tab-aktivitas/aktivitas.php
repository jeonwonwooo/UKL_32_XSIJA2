<?php
require_once 'formkoneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /CODINGAN/3-landingpageuser/login/login.php");
    exit();
}

$anggota_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

try {
    // Statistik utama
    $query_stats = "
        SELECT 
            COUNT(DISTINCT buku_id) as total_buku,
            SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END) as peminjaman_aktif,
            SUM(CASE WHEN tanggal_pinjam = ? THEN 1 ELSE 0 END) as peminjaman_hari_ini,
            SUM(CASE WHEN status = 'dikembalikan' AND tanggal_kembali = ? THEN 1 ELSE 0 END) as pengembalian_hari_ini,
            (SELECT COUNT(*) FROM denda WHERE anggota_id = ? AND status = 'belum_dibayar') as total_denda
        FROM peminjaman 
        WHERE anggota_id = ?
    ";
    $stmt_stats = $conn->prepare($query_stats);
    $stmt_stats->execute([$current_date, $current_date, $anggota_id, $anggota_id]);
    $stats = $stmt_stats->fetch();

    // Fungsi denda harian
    function hitungDendaHarian($batas_pengembalian_str) {
        if (!$batas_pengembalian_str) return ['is_terlambat' => false];
        $batas_pengembalian = new DateTime($batas_pengembalian_str);
        $today = new DateTime();
        if ($today <= $batas_pengembalian) return ['is_terlambat' => false];
        $interval = $today->diff($batas_pengembalian);
        $selisih_hari = $interval->days;
        $denda_awal = 5000;
        $peningkatan_per_hari = 2000;
        $total_denda = $denda_awal + ($selisih_hari - 1) * $peningkatan_per_hari;
        return [
            'is_terlambat' => true,
            'selisih_hari' => $selisih_hari,
            'total_denda' => $total_denda,
            'keterangan' => "Denda keterlambatan selama {$selisih_hari} hari"
        ];
    }
    

    // Update denda untuk peminjaman terlambat
    $query_terlambat = "
        SELECT p.id, p.anggota_id, p.batas_pengembalian, p.status,
               d.id as denda_id, d.status as denda_status
        FROM peminjaman p
        LEFT JOIN denda d ON p.id = d.peminjaman_id
        WHERE p.anggota_id = ?
        AND p.status = 'dipinjam'
        AND p.batas_pengembalian < ?
        AND (d.id IS NULL OR d.status = 'belum_dibayar')
    ";
    $stmt_terlambat = $conn->prepare($query_terlambat);
    $stmt_terlambat->execute([$anggota_id, $current_date]);
    $peminjaman_terlambat = $stmt_terlambat->fetchAll(PDO::FETCH_ASSOC);

    foreach ($peminjaman_terlambat as $pinjam) {
        $denda_info = hitungDendaHarian($pinjam['batas_pengembalian']);
        if ($denda_info['is_terlambat']) {
            if ($pinjam['denda_id']) {
                $stmt_update_denda = $conn->prepare("
                    UPDATE denda 
                    SET nominal = ?, keterangan = ?, tanggal_denda = ?
                    WHERE id = ?
                ");
                $stmt_update_denda->execute([
                    $denda_info['total_denda'],
                    $denda_info['keterangan'],
                    $current_date,
                    $pinjam['denda_id']
                ]);
            } else {
                $stmt_insert_denda = $conn->prepare("
                    INSERT INTO denda 
                    (peminjaman_id, anggota_id, nominal, status, tanggal_denda, keterangan)
                    VALUES (?, ?, ?, 'belum_dibayar', ?, ?)
                ");
                $stmt_insert_denda->execute([
                    $pinjam['id'],
                    $pinjam['anggota_id'],
                    $denda_info['total_denda'],
                    $current_date,
                    $denda_info['keterangan']
                ]);
            }
        }
    }

    // Ambil aktivitas terbaru
    $query_activities = "
        SELECT 
            p.id AS peminjaman_id,
            p.anggota_id,
            b.judul AS judul_buku,
            p.tanggal_pinjam,
            p.batas_pengembalian,
            p.status,
            p.status_pengajuan,
            p.jumlah_pengajuan,
            d.id AS denda_id,
            d.nominal AS denda_nominal,
            d.status AS denda_status,
            d.status_pembayaran AS denda_pembayaran,
            d.tanggal_denda,
            d.keterangan
        FROM peminjaman p
        JOIN buku b ON p.buku_id = b.id
        LEFT JOIN denda d ON p.id = d.peminjaman_id
        WHERE p.anggota_id = ?
        ORDER BY p.id DESC
        LIMIT 5
    ";
    $stmt_activities = $conn->prepare($query_activities);
    $stmt_activities->execute([$anggota_id]);
    $activities = $stmt_activities->fetchAll(PDO::FETCH_ASSOC);

    $latest_activity = !empty($activities) ? $activities[0] : null;
    $denda_harian_info = $latest_activity ? hitungDendaHarian($latest_activity['batas_pengembalian']) : ['is_terlambat' => false];
    $status_pengajuan = $latest_activity['status_pengajuan'] ?? null;
    $sisa_kesempatan = $latest_activity ? max(0, 3 - ($latest_activity['jumlah_pengajuan'] ?? 0)) : 0;
    $denda_id = $latest_activity['denda_id'] ?? null;
    $denda_nominal = $latest_activity['denda_nominal'] ?? 0;

} catch (PDOException $e) {
    $activities = [];
    $latest_activity = null;
    $denda_harian_info = ['is_terlambat' => false];
    $status_pengajuan = null;
    $sisa_kesempatan = 0;
    $denda_id = null;
    $denda_nominal = 0;
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
            margin: 20px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        .notification i {
            font-size: 18px;
        }
        .success-notification {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-notification {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .warning-notification {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .info-notification {
            background-color: #cce7ff;
            color: #004085;
            border: 1px solid #b8daff;
        }
        .btn-action {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            transition: background-color 0.3s;
        }
        .btn-action:hover {
            background-color: #0056b3;
        }
        .btn-action.red {
            background-color: #dc3545;
        }
        .btn-action.red:hover {
            background-color: #c82333;
        }
        .btn-action.disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            pointer-events: none;
        }
        .status-pengajuan-menunggu {
            color: #856404;
            font-weight: bold;
            background-color: #fff3cd;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .status-pengajuan-diterima {
            color: #155724;
            font-weight: bold;
            background-color: #d4edda;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .status-pengajuan-ditolak {
            color: #721c24;
            font-weight: bold;
            background-color: #f8d7da;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .status-denda-lunas {
            color: #155724;
            font-weight: bold;
            background-color: #d4edda;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .stats-table {
            max-width: 500px;
        }
        .stats-table td {
            padding: 10px;
        }
        .stats-table td:first-child {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Logo Perpus" />
        </div>
        <nav class="navbar">
            <ul>
                <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/aktivitas.php">Aktivitas</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php">Favorit</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.php">Kontak</a></li>
                <li class="profil"><a href="/CODINGAN/3-landingpageuser/akun/akun.php" class="akun"><i class="fas fa-user"></i></a></li>
                <li>
                    <button class="btn-logout">
                        <i class="fas fa-arrow-left"></i>
                        <a href="/CODINGAN/3-landingpageuser/layanan/layanan.php">Kembali</a>
                    </button>
                </li>
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
                    <p>Statistik ini akan diperbarui secara <i>real-time</i>. Berisikan riwayat aktivitas secara keseluruhan di perpustakaan kami.</p>
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
            <!-- Notifikasi Utama -->
            <?php
if (!empty($activities)) {
                $latest_activity = $activities[0];
                $status = $latest_activity['status'] ?? null;
                $status_pengajuan = $latest_activity['status_pengajuan'] ?? null;
                $jumlah_pengajuan = isset($latest_activity['jumlah_pengajuan']) && is_numeric($latest_activity['jumlah_pengajuan']) ? (int)$latest_activity['jumlah_pengajuan'] : 0;
                $sisa_kesempatan = ($status === 'dipinjam') ? max(0, 3 - $jumlah_pengajuan) : 0;
                $denda_id = $latest_activity['denda_id'] ?? null;
                $denda_status = $latest_activity['denda_status'] ?? 'belum_dibayar';
                $denda_nominal = $latest_activity['denda_nominal'] ?? 0;
                $denda_harian_info = hitungDendaHarian($latest_activity['batas_pengembalian']);
                $denda_pembayaran = $latest_activity['denda_pembayaran'] ?? 'pending';
            } else {
                // Default values when no activities
                $latest_activity = null;
                $status = null;
                $status_pengajuan = null;
                $jumlah_pengajuan = 0;
                $sisa_kesempatan = 0;
                $denda_id = null;
                $denda_status = null;
                $denda_nominal = 0;
                $denda_harian_info = ['is_terlambat' => false];
                $denda_pembayaran = null;
            }
?>

<?php if ($status === 'dikembalikan'): ?>
    <div class="notification success-notification">
        <i class="fas fa-check-circle"></i>
        <div class="notification-content">
            <span>Buku "<?= htmlspecialchars($latest_activity['judul_buku']) ?>" telah dikembalikan.</span>
        </div>
    </div>
<?php elseif ($status_pengajuan === 'diterima'): ?>
    <div class="notification success-notification">
        <i class="fas fa-check-circle"></i>
        <div class="notification-content">
            <span>Pengajuan pengembalian Anda telah diterima.</span>
        </div>
    </div>
<?php elseif ($denda_status === 'proses' && in_array($denda_pembayaran, ['pending', 'failed'])): ?>
    <div class="notification info-notification">
        <i class="fas fa-info-circle"></i>
        <div class="notification-content">
            <span>Pembayaran denda sedang diverifikasi admin. Mohon tunggu konfirmasi.</span>
        </div>
    </div>
<?php elseif ($denda_status === 'belum_dibayar' && $denda_pembayaran === 'failed'): ?>
    <div class="notification error-notification">
        <i class="fas fa-exclamation-circle"></i>
        <div class="notification-content">
            <span>Pembayaran denda Anda <b>GAGAL</b>. Silakan lakukan pembayaran ulang sesuai instruksi admin.</span>
        </div>
    </div>
<?php elseif ($denda_status === 'sudah_dibayar' && $denda_pembayaran === 'success'): ?>
    <div class="notification success-notification">
        <i class="fas fa-check-circle"></i>
        <div class="notification-content">
            <span>Denda sudah <b>LUNAS</b>. Terima kasih telah melakukan pembayaran.</span>
        </div>
    </div>
<?php elseif (
    $denda_status === 'belum_dibayar'
    && $denda_pembayaran === 'pending'
    && $denda_id !== null
    && $denda_nominal > 0
): ?>
    <div class="notification warning-notification">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="notification-content">
            <span>Anda memiliki denda yang belum dibayar. Segera lakukan pembayaran sebelum mengajukan pengembalian!</span>
        </div>
    </div>
<?php elseif ($status_pengajuan === 'ditolak' && $jumlah_pengajuan >= 0): ?>
    <div class="notification warning-notification">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="notification-content">
            <span>Pengajuan ditolak. Sisa kesempatan pengajuan: <?= $jumlah_pengajuan ?></span>
        </div>
    </div>
<?php elseif ($status_pengajuan === 'ditolak' && $jumlah_pengajuan <= 0): ?>
    <div class="notification error-notification">
        <i class="fas fa-exclamation-circle"></i>
        <div class="notification-content">
            <span>Pengajuan diindikasikan penipuan. Batas maksimal pengajuan (3x) telah tercapai.</span>
        </div>
    </div>
<?php elseif ($status_pengajuan === 'menunggu'): ?>
    <div class="notification info-notification">
        <i class="fas fa-info-circle"></i>
        <div class="notification-content">
            <span>Pengajuan sedang menunggu verifikasi admin.</span>
        </div>
    </div>
<?php elseif ($status === 'dipinjam' && ($status_pengajuan === null || $status_pengajuan === '-' || $status_pengajuan === '')): ?>
    <div class="notification info-notification">
        <i class="fas fa-info-circle"></i>
        <div class="notification-content">
            <span>Anda dapat mengajukan pengembalian. Sisa kesempatan: 3</span>
        </div>
    </div>
<?php else: ?>
    <div class="notification info-notification">
        <i class="fas fa-info-circle"></i>
        <div class="notification-content">
            <span>Tidak ada aktivitas baru yang perlu ditindaklanjuti.</span>
        </div>
    </div>
<?php endif; ?>
            <?php if (!empty($activities)): ?>
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
    <?php
    $status = $activity['status'] ?? null;
    $status_pengajuan = $activity['status_pengajuan'] ?? null;
    $jumlah_pengajuan = isset($activity['jumlah_pengajuan']) && is_numeric($activity['jumlah_pengajuan']) ? (int)$activity['jumlah_pengajuan'] : 0;
    $denda_id = $activity['denda_id'] ?? null;
    $denda_status = $activity['denda_status'] ?? null;
    $denda_nominal = $activity['denda_nominal'] ?? 0;
    $peminjaman_id = $activity['peminjaman_id'] ?? null;
$sisa_kesempatan = ($status === 'dipinjam') ? max(0, 3 - $jumlah_pengajuan) : 0;
    // Denda harian
    $denda_harian_info = hitungDendaHarian($activity['batas_pengembalian']);
    $display_denda = $denda_nominal;
    $ada_denda_harian = $denda_harian_info['is_terlambat'] && $status === 'dipinjam' && $denda_status !== 'sudah_dibayar';
    if ($ada_denda_harian) {
        $display_denda = $denda_harian_info['total_denda'];
    }

    // Denda tetap (misal 50k) jika ada denda_id dan nominalnya >= 50000
    $ada_denda_tetap = $denda_id && $denda_nominal >= 50000 && $denda_status !== 'sudah_dibayar';

    // Ada denda apapun
    $ada_denda = ($ada_denda_harian || $ada_denda_tetap) && $denda_status !== 'sudah_dibayar';
    ?>
    <tr>
        <td><?= $index + 1 ?></td>
        <td><?= htmlspecialchars($activity['judul_buku']) ?></td>
        <td><?= htmlspecialchars($activity['tanggal_pinjam']) ?></td>
        <td><?= htmlspecialchars($status) ?></td>
        <td><?= htmlspecialchars($status_pengajuan ?? '-') ?></td>
        <td><?= $status === 'dipinjam' ? $jumlah_pengajuan : '-' ?></td>
        <td>
            <?php if ($ada_denda_harian): ?>
                <span style="color: #dc3545; font-weight: bold;">
                    Rp<?= number_format($display_denda, 0, ',', '.') ?>
                </span>
                <br><small style="color: #856404;">
                    (Terlambat <?= $denda_harian_info['selisih_hari'] ?> hari)
                </small>
            <?php elseif ($ada_denda_tetap): ?>
                <span style="color: #dc3545; font-weight: bold;">
                    Rp<?= number_format($denda_nominal, 0, ',', '.') ?> (Denda Tetap)
                </span>
            <?php elseif ($denda_id): ?>
                <?php if ($denda_status === 'sudah_dibayar'): ?>
                    <span class="status-denda-lunas">
                        Rp<?= number_format($denda_nominal, 0, ',', '.') ?> (LUNAS)
                    </span>
                <?php else: ?>
                    <span style="color: #dc3545; font-weight: bold;">
                        Rp<?= number_format($denda_nominal, 0, ',', '.') ?>
                    </span>
                    <br><small>(<?= htmlspecialchars($denda_status) ?>)</small>
                <?php endif; ?>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
        <td>
    <?php if ($status === 'dikembalikan'): ?>
        <span class="status-pengajuan-diterima">Buku telah dikembalikan</span>
    <?php elseif ($ada_denda): ?>
    <?php if ($denda_status === 'proses' && in_array($denda_pembayaran, ['pending', 'failed'])): ?>
        <span class="status-pengajuan-menunggu">Menunggu Verifikasi Admin</span>
    <?php else: ?>
        <a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/denda/bayar_denda.php?id=<?= htmlspecialchars($denda_id) ?>" class="btn-action red">
            <?= $denda_pembayaran === 'failed' ? 'Bayar Lagi' : 'Bayar Denda' ?>
        </a>
    <?php endif; ?>
    <?php elseif ($status_pengajuan === 'menunggu'): ?>
        <span class="status-pengajuan-menunggu">Menunggu Verifikasi Admin</span>
    <?php elseif ($status_pengajuan === 'ditolak' && $sisa_kesempatan > 0): ?>
        <a href="ajukan-kembali.php?id=<?= htmlspecialchars($peminjaman_id) ?>" class="btn-action">Ajukan Kembali</a>
    <?php elseif ($status_pengajuan === 'ditolak' && $sisa_kesempatan <= 0): ?>
        <span class="status-pengajuan-ditolak">BATAS<br>PENGAJUAN<br>TERCAPAI</span>
    <?php elseif ($status === 'dipinjam'): ?>
        <a href="ajukan-kembali.php?id=<?= htmlspecialchars($peminjaman_id) ?>" class="btn-action">Ajukan Pengembalian</a>
    <?php else: ?>
        <span class="btn-action disabled">Tidak Ada Aksi</span>
    <?php endif; ?>
</td>
    </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align:center; padding: 20px;">
                    <p>Tidak ada aktivitas peminjaman.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="laporan-kerusakan">
            <div class="left-column">
                <iframe src="https://docs.google.com/forms/d/e/1FAIpQLSeeHmEzoQgW-ZkDoWk7NKS_2sXPFqMoPTHLIkQ-Dd5-kF7xwQ/viewform?embedded=true" allowfullscreen loading="lazy"></iframe>
            </div>
            <div class="right-column">
                <h3>Laporan Kerusakan</h3>
                <p>
                    Jika Anda menemukan kerusakan pada buku atau fasilitas perpustakaan, silakan laporkan melalui formulir di samping.
                    Kami akan memproses laporan Anda secepat mungkin untuk memastikan kenyamanan pengguna perpustakaan.
                </p>
            </div>
        </div>
    </main>
    <footer class="footer">
    <div class="container">
      <div class="left">
        <img src="logo.png" alt="Library of Riverhill Senior High School logo" />
        <p>
          Perpustakaan SMA Rivenhill berkomitmen menjadi pusat pembelajaran yang mendukung visi sekolah dalam menciptakan generasi berwawasan luas. Kami buka setiap hari Senin-Jumat pukul 07.30-15.30 WIB.
        </p>
        <div class="social-icons">
          <a href="https://wa.me/6285936164597" target="_blank"><i class="fab fa-whatsapp"></i></a>
          <a href="https://www.linkedin.com/in/syarivatun-nisa-i-nur-aulia-3ab52b2bb/" target="_blank"><i class="fab fa-linkedin"></i></a>
          <a href="https://instagram.com/jeonwpnwoo" target="_blank"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
      <div class="right">
        <h2>Tautan Fungsional</h2>
        <ul>
          <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
          <li><a href="/CODINGAN/3-landingpageuser/layanan/layanan.php">Layanan</a></li>
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