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
    // Query statistik utama (dioptimasi)
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

    // Fungsi hitung denda harian
    function hitungDendaHarian($batas_pengembalian_str) {
        if (!$batas_pengembalian_str) return ['is_terlambat' => false];

        $batas_pengembalian = new DateTime($batas_pengembalian_str);
        $today = new DateTime();

        if ($today <= $batas_pengembalian) {
            return ['is_terlambat' => false];
        }

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

    // Proses update denda untuk semua peminjaman yang terlambat
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

    // Aktivitas Terbaru dengan informasi denda terupdate
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

    // Ambil aktivitas terbaru untuk notifikasi
    $latest_activity = !empty($activities) ? $activities[0] : null;
    $denda_harian_info = $latest_activity ? hitungDendaHarian($latest_activity['batas_pengembalian']) : ['is_terlambat' => false];

    // Variabel untuk notifikasi
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
            margin-bottom: 15px;
            border-radius: 4px;
            display: flex;
            align-items: center;
        }
        .notification.success-notification {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .notification.error-notification {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .notification.warning-notification {
            background-color: #fff3cd;
            color: #856404;
            border-left: 5px solid #ffc107;
        }
        .notification.info-notification {
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
        .btn-action {
            display: inline-block;
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .btn-action.red {
            background-color: #dc3545;
        }
        .btn-action.disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .status-pengajuan-diterima {
            color: #28a745;
            font-weight: bold;
        }
        .status-pengajuan-menunggu {
            color: #007bff;
            font-weight: bold;
        }
        .status-pengajuan-ditolak {
            color: #dc3545;
            font-weight: bold;
        }
        .status-denda-lunas {
            color: #28a745;
            font-weight: bold;
            background-color: #d4edda;
            padding: 3px 8px;
            border-radius: 3px;
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
            <!-- Notifikasi Denda Harian - hanya tampil jika denda belum diproses -->
            <?php if ($denda_harian_info['is_terlambat'] && $latest_activity && $latest_activity['status'] === 'dipinjam' && (!$latest_activity['denda_status'] || $latest_activity['denda_status'] !== 'proses')): ?>
                <div class="notification warning-notification">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="notification-content">
                        <span>Buku "<?= htmlspecialchars($latest_activity['judul_buku']) ?>" terlambat <?= $denda_harian_info['selisih_hari'] ?> hari.</span>
                        <p>Total denda harian: Rp<?= number_format($denda_harian_info['total_denda'], 0, ',', '.') ?></p>
                        <p><small>Denda akan terus bertambah Rp2.000 setiap hari sampai buku dikembalikan.</small></p>
                    </div>
                </div>
            <?php endif; ?>
            
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
                <?php elseif ($latest_activity && ($latest_activity['denda_pembayaran'] === 'success' || $latest_activity['denda_pembayaran'] === 'sudah_dibayar')): ?>
                    <div class="notification success-notification">
                        <i class="fas fa-check-circle"></i>
                        <div class="notification-content">
                            <span>Denda sudah dibayar. Terima kasih telah jujur!</span>
                        </div>
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
            
            <!-- Notifikasi Denda Diproses -->
            <?php if ($latest_activity && !empty($latest_activity['denda_status']) && $latest_activity['denda_status'] === 'proses'): ?>
                <div class="notification info-notification">
                    <i class="fas fa-info-circle"></i>
                    <div class="notification-content">
                        <span>Pembayaran denda sedang diproses. Mohon tunggu konfirmasi dari admin.</span>
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
                            // Hitung denda real-time untuk setiap aktivitas
                            $current_denda_info = hitungDendaHarian($activity['batas_pengembalian']);
                            $display_denda = $activity['denda_nominal'] ?? 0;
                            
                            // Jika terlambat dan belum dibayar, gunakan perhitungan real-time
                            if ($current_denda_info['is_terlambat'] && 
                                $activity['status'] === 'dipinjam' && 
                                (!$activity['denda_pembayaran'] || 
                                 !in_array($activity['denda_pembayaran'], ['success', 'sudah_dibayar']))) {
                                $display_denda = $current_denda_info['total_denda'];
                            }
                            
                            // Hitung sisa kesempatan - jika ada denda maka 0
                            $sisa_kesempatan_row = 0;
                            if ($activity['status'] === 'dipinjam') {
                                if (!empty($activity['denda_id']) && $activity['denda_status'] !== 'sudah_dibayar') {
                                    $sisa_kesempatan_row = 0;
                                } else {
                                    $sisa_kesempatan_row = max(0, 3 - ($activity['jumlah_pengajuan'] ?? 0));
                                }
                            }
                            
                            // Cek apakah denda sudah dibayar
                            $is_denda_lunas = ($activity['denda_pembayaran'] === 'success' || $activity['denda_pembayaran'] === 'sudah_dibayar');
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($activity['judul_buku']) ?></td>
                                <td><?= htmlspecialchars($activity['tanggal_pinjam']) ?></td>
                                <td><?= htmlspecialchars($activity['status']) ?></td>
                                <td><?= htmlspecialchars($activity['status_pengajuan'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    if ($activity['status'] === 'dipinjam') {
                                        echo $sisa_kesempatan_row;
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($current_denda_info['is_terlambat'] && $activity['status'] === 'dipinjam'): ?>
                                        <?php if ($is_denda_lunas): ?>
                                            <span class="status-denda-lunas">
                                                Rp<?= number_format($activity['denda_nominal'], 0, ',', '.') ?> (LUNAS)
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #dc3545; font-weight: bold;">
                                                Rp<?= number_format($display_denda, 0, ',', '.') ?>
                                            </span>
                                            <br><small style="color: #856404;">
                                                (Terlambat <?= $current_denda_info['selisih_hari'] ?> hari)
                                            </small>
                                        <?php endif; ?>
                                    <?php elseif (!empty($activity['denda_id'])): ?>
                                        <?php if ($is_denda_lunas): ?>
                                            <span class="status-denda-lunas">
                                                Rp<?= number_format($activity['denda_nominal'], 0, ',', '.') ?> (LUNAS)
                                            </span>
                                        <?php else: ?>
                                            Rp<?= number_format($activity['denda_nominal'], 0, ',', '.') ?>
                                            <br><small>(<?= htmlspecialchars($activity['denda_status']) ?>)</small>
                                        <?php endif; ?>
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
                                    $peminjaman_id = $activity['peminjaman_id'] ?? null;

                                    if ($status === 'dikembalikan') {
                                        echo '<span class="status-pengajuan-diterima">Buku telah dikembalikan</span>';
                                    } elseif ($is_denda_lunas) {
                                        // Jika denda sudah dibayar, tampilkan status lunas dan beri opsi ajukan pengembalian
                                        if ($status === 'dipinjam') {
                                            $sisa_kesempatan_after_paid = max(0, 3 - ($activity['jumlah_pengajuan'] ?? 0));
                                            if ($status_pengajuan === 'menunggu') {
                                                echo '<span class="status-pengajuan-menunggu">Menunggu Verifikasi Admin</span>';
                                            } elseif ($sisa_kesempatan_after_paid > 0) {
                                                if ($status_pengajuan === 'ditolak') {
                                                    echo '<a href="ajukan-kembali.php?id=' . htmlspecialchars($peminjaman_id) . '" class="btn-action">Ajukan Kembali</a>';
                                                } else {
                                                    echo '<a href="ajukan-kembali.php?id=' . htmlspecialchars($peminjaman_id) . '" class="btn-action">Ajukan Pengembalian</a>';
                                                }
                                            } else {
                                                echo '<span class="status-denda-lunas">Denda Sudah Dibayar</span>';
                                            }
                                        } else {
                                            echo '<span class="status-denda-lunas">Denda Sudah Dibayar</span>';
                                        }
                                    } elseif (!empty($denda_id)) {
                                        // Prioritaskan status denda jika ada dan belum lunas
                                        if ($denda_status === 'proses') {
                                            echo '<span class="btn-action disabled">Denda Diproses</span>';
                                        } elseif ($denda_pembayaran === 'failed') {
                                            echo '<a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/denda/bayar_denda.php?id=' . htmlspecialchars($denda_id) . '" class="btn-action red">Bayar Lagi</a>';
                                        } elseif ($denda_pembayaran === 'belum_dibayar' || $denda_pembayaran === 'pending' || empty($denda_pembayaran)) {
                                            echo '<a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/denda/bayar_denda.php?id=' . htmlspecialchars($denda_id) . '" class="btn-action red">Bayar Denda</a>';
                                        } else {
                                            echo '<span class="btn-action disabled">Status Denda Tidak Dikenali</span>';
                                        }
                                    } elseif ($current_denda_info['is_terlambat'] && $status === 'dipinjam') {
                                        // Jika terlambat tapi belum ada denda record, buat link bayar denda
                                        echo '<a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/denda/bayar_denda.php?id=' . htmlspecialchars($peminjaman_id) . '" class="btn-action red">Bayar Denda</a>';
                                    } elseif ($status === 'dipinjam' && $sisa_kesempatan_row > 0) {
                                        if ($status_pengajuan === 'ditolak') {
                                            echo '<a href="ajukan-kembali.php?id=' . htmlspecialchars($peminjaman_id) . '" class="btn-action">Ajukan Kembali</a>';
                                        } elseif ($status_pengajuan === 'menunggu') {
                                            echo '<span class="status-pengajuan-menunggu">Menunggu Verifikasi Admin</span>';
                                        } else {
                                            if ($sisa_kesempatan_row == 3) {
                                                echo '<a href="ajukan-kembali.php?id=' . htmlspecialchars($peminjaman_id) . '" class="btn-action">Ajukan Pengembalian</a>';
                                            } else {
                                                echo '<a href="ajukan-kembali.php?id=' . htmlspecialchars($peminjaman_id) . '" class="btn-action">Ajukan Kembali</a>';
                                            }
                                        }
                                    } elseif ($status === 'dipinjam' && $sisa_kesempatan_row <= 0) {
                                        if ($status_pengajuan === 'menunggu') {
                                            echo '<span class="status-pengajuan-menunggu">Menunggu Verifikasi Admin</span>';
                                        } elseif ($status_pengajuan === 'ditolak') {
                                            echo '<span class="status-pengajuan-ditolak">Pengajuan Ditolak</span>';
                                        } else {
                                            echo '<span class="btn-action disabled">Tidak Ada Aksi</span>';
                                        }
                                    } elseif ($status_pengajuan === 'menunggu') {
                                        echo '<span class="status-pengajuan-menunggu">Menunggu Verifikasi Admin</span>';
                                    } elseif ($status_pengajuan === 'ditolak' && $sisa_kesempatan_row > 0) {
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
                <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Repudiandae omnis molestias nobis.</p>
                <div class="social-icons">
                    <a href="https://wa.me/6285936164597" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.linkedin.com/in/syarivatun-nisa-i-nur-aulia-3ab52b2bb/" target="_blank"><i class="fab fa-linkedin"></i></a>
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