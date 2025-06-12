<?php
include 'formkoneksi.php';

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.php?error=haruslogindulu.");
    exit();
}

$user_id = $_SESSION['user_id'];
$pesan_profil = '';
$pesan_password = '';

try {
    // Ambil data pengguna dari database
    $stmt = $conn->prepare("SELECT * FROM anggota WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User tidak ditemukan.");
    }

    // Fungsi untuk mendapatkan foto profil
    function getFotoProfile($user) {
        $default_photo = '/CODINGAN/assets/default_profile.jpg';
        
        if (empty($user['foto_profil'])) {
            return $default_photo;
        }
        
        $photo_path = $_SERVER['DOCUMENT_ROOT'] . $user['foto_profil'];
        if (!file_exists($photo_path)) {
            return $default_photo;
        }
        
        return $user['foto_profil'];
    }

    $foto_profil = getFotoProfile($user);

    // Proses form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // Handle foto profil upload
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "/CODINGAN/4-landingpageadmin/uploads/";
            $abs_target_dir = $_SERVER['DOCUMENT_ROOT'] . $target_dir;

            // Buat folder jika belum ada
            if (!file_exists($abs_target_dir)) {
                mkdir($abs_target_dir, 0777, true);
            }

            // Validasi ekstensi file
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_extension = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));

            if (in_array($file_extension, $allowed_ext)) {
                // Validasi ukuran file (maksimal 5MB)
                if ($_FILES['foto_profil']['size'] <= 5 * 1024 * 1024) {
                    $new_filename = "profile_{$user_id}_" . time() . "." . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    $abs_target_file = $abs_target_dir . $new_filename;

                    if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $abs_target_file)) {
                        // Hapus foto lama jika bukan default
                        if ($user['foto_profil'] && $user['foto_profil'] != '/CODINGAN/assets/default_profile.jpg') {
                            $old_file = $_SERVER['DOCUMENT_ROOT'] . $user['foto_profil'];
                            if (file_exists($old_file)) {
                                unlink($old_file);
                            }
                        }

                        // Update database
                        $stmt_update = $conn->prepare("UPDATE anggota SET foto_profil = ? WHERE id = ?");
                        if ($stmt_update->execute([$target_file, $user_id])) {
                            $user['foto_profil'] = $target_file;
                            $foto_profil = $target_file;
                            $pesan_profil = "Foto profil berhasil diperbarui!";
                        } else {
                            $pesan_profil = "Gagal menyimpan foto profil ke database.";
                        }
                    } else {
                        $pesan_profil = "Gagal upload foto profil.";
                    }
                } else {
                    $pesan_profil = "Ukuran file terlalu besar. Maksimal 5MB.";
                }
            } else {
                $pesan_profil = "Format file tidak didukung. Hanya JPG, JPEG, PNG, GIF, WEBP.";
            }
        }
        
        // Handle hapus foto profil
        elseif (isset($_POST['hapus_foto_profil'])) {
            if ($user['foto_profil'] && $user['foto_profil'] != '/CODINGAN/assets/default_profile.jpg') {
                $old_file = $_SERVER['DOCUMENT_ROOT'] . $user['foto_profil'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            
            // Update database untuk menghapus foto profil
            $stmt_update = $conn->prepare("UPDATE anggota SET foto_profil = NULL WHERE id = ?");
            if ($stmt_update->execute([$user_id])) {
                $user['foto_profil'] = null;
                $foto_profil = '/CODINGAN/assets/default_profile.jpg';
                $pesan_profil = "Foto profil berhasil dihapus!";
            } else {
                $pesan_profil = "Gagal menghapus foto profil.";
            }
        }
        
        // Handle update profil data
elseif (isset($_POST['update_profil'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    
    if (!empty($nama) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Cek apakah email sudah digunakan user lain
        $stmt_check = $conn->prepare("SELECT id FROM anggota WHERE email = ? AND id != ?");
        $stmt_check->execute([$email, $user_id]);
        $stmt_check = $conn->prepare("SELECT id FROM anggota WHERE username = ? AND id != ?");
        $stmt_check->execute([$username, $user_id]);
        
        if ($stmt_check->rowCount() == 0) {
            $stmt_update = $conn->prepare("UPDATE anggota SET nama = ?, email = ?, username = ? WHERE id = ?");
            if ($stmt_update->execute([$nama, $email, $username, $user_id])) {
                $user['nama'] = $nama;
                $user['email'] = $email;
                $user['username'] = $username;
                $pesan_profil = "Profil berhasil diperbarui!";
            } else {
                $pesan_profil = "Gagal memperbarui profil.";
            }
        } else {
            $pesan_profil = "Email sudah digunakan oleh pengguna lain.";
        }
    } else {
        $pesan_profil = "Data harus diisi dengan benar.";
    }
}
        
        // Handle ganti password
        elseif (isset($_POST['ganti_password'])) {
            $password_lama = $_POST['password_lama'];
            $password_baru = $_POST['password_baru'];
            $konfirmasi = $_POST['konfirmasi'];
            
            if (password_verify($password_lama, $user['password'])) {
                if ($password_baru === $konfirmasi) {
                    if (strlen($password_baru) >= 6) {
                        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                        $stmt_update = $conn->prepare("UPDATE anggota SET password = ? WHERE id = ?");
                        if ($stmt_update->execute([$password_hash, $user_id])) {
                            $pesan_password = "Password berhasil diubah!";
                        } else {
                            $pesan_password = "Gagal mengubah password.";
                        }
                    } else {
                        $pesan_password = "Password baru minimal 6 karakter.";
                    }
                } else {
                    $pesan_password = "Konfirmasi password tidak cocok.";
                }
            } else {
                $pesan_password = "Password lama salah.";
            }
        }
        
        // Handle hapus akun
        elseif (isset($_POST['hapus_akun'])) {
            // Hapus foto profil jika ada
            if ($user['foto_profil'] && $user['foto_profil'] != '/CODINGAN/assets/default_profile.jpg') {
                $old_file = $_SERVER['DOCUMENT_ROOT'] . $user['foto_profil'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            
            // Hapus akun dari database
            $stmt_delete = $conn->prepare("DELETE FROM anggota WHERE id = ?");
            if ($stmt_delete->execute([$user_id])) {
                session_destroy();
                header("Location: login.php?pesan=akun_dihapus");
                exit();
            }
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
    <style>
        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid;
        }
        .alert.success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .alert.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .image-upload-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 15px 0;
        }
        .image-upload img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
        }
        .photo-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        #foto-profil {
            display: none;
        }
    </style>
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
            <a href="/CODINGAN/z-yakinlogout/formyakin.php">Keluar</a>
        </nav>
    </div>
</header>

<main class="account-container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="profile-card">
            <div class="profile-image">
                <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Foto Profil" id="sidebar-profile-img">
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
                <li><a href="/CODINGAN/z-yakinlogout/formyakin.php"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <section class="main-content">

        <!-- Profile Section -->
        <section id="profile" class="content-section<?= !isset($_GET['tab']) ? ' default' : '' ?>">
            <h2><i class="fas fa-user-edit"></i> Edit Profil</h2>
            <?php if (!empty($pesan_profil)): ?>
                <div class="alert <?= strpos($pesan_profil, 'berhasil') !== false ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($pesan_profil) ?>
                </div>
            <?php endif; ?>
            
            <!-- Foto Profil Section -->
            <div class="form-group">
                <label>Foto Profil:</label>
                <div class="image-upload-container">
                    <div class="image-upload">
                        <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Foto Profil" id="profile-preview">
                    </div>
                    <div class="photo-buttons">
                        <button type="button" class="btn-secondary" onclick="document.getElementById('foto-profil').click()">
                            <i class="fas fa-camera"></i> Ubah Foto
                        </button>
                        <?php if ($user['foto_profil'] && $user['foto_profil'] != '/CODINGAN/assets/default_profile.jpg'): ?>
                        <form method="POST" style="margin: 0;" onsubmit="return confirm('Yakin ingin menghapus foto profil?');">
                            <input type="hidden" name="hapus_foto_profil" value="1">
                            <button type="submit" class="btn-danger">
                                <i class="fas fa-trash"></i> Hapus Foto
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form Upload Foto -->
            <form method="POST" enctype="multipart/form-data" id="foto-form" style="display: none;">
                <input type="file" name="foto_profil" id="foto-profil" accept=".jpg,.jpeg,.png,.gif,.webp" onchange="previewAndSubmit()">
            </form>

            <!-- Form Update Profil -->
            <form method="POST" class="profile-form">
                <input type="hidden" name="update_profil" value="1">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </form>
        </section>

        <!-- Ganti Password -->
        <section id="ganti-password" class="content-section">
            <h2><i class="fas fa-key"></i> Ganti Password</h2>
            <?php if (!empty($pesan_password)): ?>
                <div class="alert <?= strpos($pesan_password, 'berhasil') !== false ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($pesan_password) ?>
                </div>
            <?php endif; ?>
            <form method="POST" class="profile-form">
                <input type="hidden" name="ganti_password" value="1">
                <div class="form-group">
                    <label>Password Lama</label>
                    <input type="password" name="password_lama" required>
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password_baru" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi" required minlength="6">
                </div>
                <button type="submit" class="btn-primary">Ganti Password</button>
            </form>
        </section>

        <!-- Quick Access -->
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

<script>
function previewAndSubmit() {
    const input = document.getElementById('foto-profil');
    const file = input.files[0];
    
    if (file) {
        // Validasi ukuran file (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 5MB.');
            input.value = '';
            return;
        }
        
        // Validasi tipe file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Hanya JPG, JPEG, PNG, GIF, WEBP.');
            input.value = '';
            return;
        }
        
        // Preview gambar
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
            document.getElementById('sidebar-profile-img').src = e.target.result;
        }
        reader.readAsDataURL(file);
        
        // Submit form
        document.getElementById('foto-form').submit();
    }
}

// Handle section navigation
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('.navbar-menu a[href^="#"], .profile-menu a[href^="#"]');
    const sections = document.querySelectorAll('.content-section');
    
    function showSection(targetId) {
        sections.forEach(section => {
            section.style.display = 'none';
        });
        
        const targetSection = document.querySelector(targetId);
        if (targetSection) {
            targetSection.style.display = 'block';
        }
        
        // Update active link
        links.forEach(link => {
            link.classList.remove('active');
        });
        document.querySelectorAll(`a[href="${targetId}"]`).forEach(link => {
            link.classList.add('active');
        });
    }
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            showSection(targetId);
        });
    });
    
    // Show default section
    if (window.location.hash) {
        showSection(window.location.hash);
    } else {
        showSection('#profile');
    }
});
</script>

</body>
</html>