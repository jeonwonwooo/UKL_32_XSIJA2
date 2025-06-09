<?php
session_start();
require_once 'formkoneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Ambil data pengguna dari database
    $stmt = $conn->prepare("SELECT * FROM anggota WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User tidak ditemukan.");
    }

    // Ganti password
if (isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi)) {
        $pesan_password = "Semua kolom harus diisi.";
    } elseif ($password_lama !== $user['password']) {
        $pesan_password = "Password lama salah.";
    } elseif ($password_baru !== $konfirmasi) {
        $pesan_password = "Konfirmasi password tidak cocok.";
    } else {
        $stmt_pw = $conn->prepare("UPDATE anggota SET password = ? WHERE id = ?");
        $stmt_pw->execute([$password_baru, $user['id']]);
        $pesan_password = "Password berhasil diganti!";
        
        // Update password di variabel session (opsional)
        $user['password'] = $password_baru;
    }
}


    // Hapus akun
    if (isset($_POST['hapus_akun'])) {
        $stmt_del = $conn->prepare("DELETE FROM anggota WHERE id = ?");
        $stmt_del->execute([$user_id]);
        session_destroy();
        header("Location: /CODINGAN/3-landingpageuser/beranda/beranda.php?akun_dihapus=1");
        exit();
    }

    // Update Profil
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ganti_password']) && !isset($_POST['hapus_akun'])) {
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        $foto_profil = $user['foto_profil'];

        // Upload foto profil
        if (!empty($_FILES['foto_profil']['name'])) {
            $target_dir = "/CODINGAN/4-landingpageadmin/uploads/";
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $target_dir)) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $target_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
            $new_filename = "profile_{$user_id}_" . time() . ".{$file_extension}";
            $target_file = $target_dir . $new_filename;
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $target_file)) {
                $foto_profil = $target_file;
                if ($user['foto_profil'] && $user['foto_profil'] != '/CODINGAN/assets/default_profile.jpg') {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . $user['foto_profil']);
                }
            }
        }

        // Validasi input
        if (empty($nama) || empty($email)) {
            $pesan_profil = "Nama dan email harus diisi.";
        } else {
            $stmt_update = $conn->prepare("UPDATE anggota SET nama = ?, email = ?, foto_profil = ? WHERE id = ?");
            $stmt_update->execute([$nama, $email, $foto_profil, $user_id]);
            header("Location: akun.php?status=success#profile");
            exit();
        }
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun</title>
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>  
    <link rel="stylesheet" href="akun.css">
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-container">
        <a href="#profile" class="navbar-brand"><i class="fas fa-user-circle"></i> My Account</a>
        <nav class="navbar-menu">
            <a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Kembali Beranda</a>
            <a href="#profile" class="<?= isset($_GET['tab']) && $_GET['tab'] === 'profile' ? 'active' : '' ?>">Edit Profil</a>
            <a href="#ganti-password">Ganti Password</a>
            <a href="#quick-access">Akses Cepat</a>
            <a href="#hapus-akun">Hapus Akun</a>
            <a href="/CODINGAN/3-landingpageuser/layanan/logout.php">Keluar</a>
        </nav>
    </div>
</header>

<main class="account-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="profile-card">
            <div class="profile-image">
                <?php
                if (!empty($user['foto_profil']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $user['foto_profil'])) {
                    $foto_profil = $user['foto_profil'];
                } else {
                    $foto_profil = "/CODINGAN/assets/default_profile.jpg";
                }
                ?>
                <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Foto Profil">
            </div>
            <h3><?= htmlspecialchars($user['nama']) ?></h3>
            <p><?= htmlspecialchars($user['email']) ?></p>
        </div>
        <nav class="profile-menu">
            <ul>
                <li><a href="#profile" class="<?= isset($_GET['tab']) && $_GET['tab'] === 'profile' ? 'active' : '' ?>"><i class="fas fa-user-edit"></i> Edit Profil</a></li>
                <li><a href="#ganti-password"><i class="fas fa-key"></i> Ganti Password</a></li>
                <li><a href="#quick-access"><i class="fas fa-bolt"></i> Akses Cepat</a></li>
                <li><a href="#hapus-akun"><i class="fas fa-trash"></i> Hapus Akun</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/layanan/logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <section class="main-content">

        <!-- Profile Section -->
        <section id="profile" class="content-section<?= !isset($_GET['tab']) ? ' default' : '' ?>">
            <h2><i class="fas fa-user-edit"></i> Edit Profil</h2>
            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div class="success-message">Profil berhasil diperbarui!</div>
            <?php elseif (isset($pesan_profil)): ?>
                <div class="alert"><?= htmlspecialchars($pesan_profil) ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="profile-form">
                <div class="form-group">
                    <label>Foto Profil Saat Ini:</label>
                    <div class="image-upload">
                        <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Foto Profil">
                    </div>
                </div>
                <div class="form-group">
                    <label for="foto-profil">Ubah Foto Profil</label>
                    <input type="file" name="foto_profil" id="foto-profil">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </form>
        </section>

        <!-- Ganti Password -->
        <section id="ganti-password" class="content-section">
            <h2><i class="fas fa-key"></i> Ganti Password</h2>
            <?php if (isset($pesan_password)) echo "<div class='alert'>".htmlspecialchars($pesan_password)."</div>"; ?>
            <form method="POST" class="profile-form">
                <input type="hidden" name="ganti_password" value="1">
                <div class="form-group">
                    <label>Password Lama</label>
                    <input type="password" name="password_lama" required>
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password_baru" required>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi" required>
                </div>
                <button type="submit" class="btn-primary">Ganti Password</button>
            </form>
        </section>

        <!-- Akses Cepat -->
        <section id="quick-access" class="content-section">
            <h2><i class="fas fa-bolt"></i> Akses Cepat</h2>
            <div class="quick-access-grid">
                <a href="/CODINGAN/3-landingpageuser/layanan/sirkulasi/detailbuku/favorit.php" class="quick-access-card">
                    <div class="quick-access-icon"><i class="fas fa-heart"></i></div>
                    <h3>Favorit</h3>
                    <p>Lihat daftar favorit Anda</p>
                </a>
                <a href="/CODINGAN/3-landingpageuser/layanan/tab-aktivitas/aktivitas.php" class="quick-access-card">
                    <div class="quick-access-icon"><i class="fas fa-history"></i></div>
                    <h3>Aktivitas</h3>
                    <p>Riwayat aktivitas Anda</p>
                </a>
            </div>
        </section>

        <!-- Hapus Akun -->
        <section id="hapus-akun" class="content-section">
            <h2><i class="fas fa-trash"></i> Hapus Akun</h2>
            <div class="warning-box">
                <h3><i class="fas fa-exclamation-triangle"></i> Peringatan!</h3>
                <p>Menghapus akun akan menghapus semua data Anda secara permanen. Tindakan ini tidak dapat dibatalkan.</p>
                <form method="POST" onsubmit="return confirm('Yakin ingin menghapus akun? Data tidak bisa dikembalikan!');">
                    <input type="hidden" name="hapus_akun" value="1">
                    <button type="submit" class="btn-danger">Hapus Akun Permanen</button>
                </form>
            </div>
        </section>

    </section>

</main>

</body>
</html>i