<?php
session_start();
require_once 'formkoneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    die("Harus login dulu!");
}

$user_id = $_SESSION['user_id'];

try {
    // Ambil data user
    $stmt = $conn->prepare("SELECT * FROM anggota WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User tidak ditemukan.");
    }

    // Proses update profil jika ada POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        
        // Handle upload foto profil
        $foto_profil = $user['foto_profil']; // Default ke foto yang sudah ada
        
        if (!empty($_FILES['foto_profil']['name'])) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
            $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Cek apakah file adalah gambar
            $check = getimagesize($_FILES['foto_profil']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
                    $foto_profil = $target_file;
                    
                    // Hapus foto lama jika bukan default
                    if ($user['foto_profil'] && $user['foto_profil'] != 'default_profile.jpg') {
                        unlink($user['foto_profil']);
                    }
                }
            }
        }

        // Validasi input
        if (empty($nama) || empty($email)) {
            die("Nama dan email harus diisi.");
        }

        // Update data ke database
        $stmt_update = $conn->prepare("UPDATE anggota SET nama = ?, email = ?, foto_profil = ? WHERE id = ?");
        $stmt_update->execute([$nama, $email, $foto_profil, $user_id]);

        header("Location: akun.php?status=success");
        exit();
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<?= isset($_GET['status']) && $_GET['status'] === 'success' ? '<p style="color: green;">Profil berhasil diperbarui!</p>' : '' ?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Akun</title>
    <link rel="stylesheet" href="akun.css">
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css " />
</head>

<body>

    <header>
        <div class="logo">
            <img src="/CODINGAN/assets/logo.png" alt="Logo Perpus" />
        </div>
        <nav class="navbar">
            <ul>
                <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
                <li><a href="#">Katalog</a></li>
                <li><a href="#">Aktivitas</a></li>
                <li><a href="#">Favorit</a></li>
                <li><a href="/CODINGAN/3-landingpageuser/kontak/kontak.html">Kontak</a></li>
                <li class="profil"><a href="#"><i class="fas fa-user"></i></a></li>
                <li>
                    <button class="btn-logout">
                        <i class="fas fa-arrow-left"></i>
                        <a href="/CODINGAN/3-landingpageuser/beranda/beranda.html">Kembali</a>
                    </button>
                </li>
            </ul>
        </nav>
    </header>

    <main class="account-details">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile-section">
                <h2>Profil</h2>
                <ul>
                    <li><a href="#profile"><i class="fas fa-user"></i> Edit Profil</a></li>
                    <li><a href="#logout"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <section class="main-content">
            <!-- Profile Section -->
            <section id="profile" class="profile-section">
                <h2>Edit Profil</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="foto-profil">Foto Profil:</label>
                        <?php 
                        // Cek apakah user punya foto profil
                        if (!empty($user['foto_profil']) && file_exists($user['foto_profil'])) {
                            $foto_profil = $user['foto_profil'];
                        } else {
                            $foto_profil = "/CODINGAN/assets/default_profile.jpg";
                        }
                        ?>
                        <img src="<?= htmlspecialchars($foto_profil) ?>" alt="Foto Profil" width="100" style="border-radius: 50%;">
                        <input type="file" name="foto_profil" id="foto-profil" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Lengkap:</label>
                        <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <button type="submit" class="btn-edit">Simpan Perubahan</button>
                </form>
            </section>

            <!-- Logout Section -->
            <section id="logout" class="logout-section">
                <h2>Keluar</h2>
                <form action="/CODINGAN/3-landingpageuser/layanan/logout.php" method="POST">
                    <button type="submit" class="btn-logout">Keluar</button>
                </form>
            </section>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="left">
                <img src="/CODINGAN/assets/logo.png" alt="Library of Riverhill Senior High School logo" />
                <p>
                    Lorem ipsum dolor sit amet consectetur adipiscing elit. Repudiandae
                    omnis molestias nobis. Lorem ipsum dolor sit amet consectetur
                    adipiscing elit. Repudiandae omnis molestias nobis.
                </p>
                <div class="social-icons">
                    <a href="https://wa.me/6285936164597 " target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.linkedin.com/in/syarivatun-nisa-i-nur-aulia-3ab52b2bb/ " target="_blank"><i
                            class="fab fa-linkedin"></i></a>
                    <a href="https://instagram.com/jeonwpnwoo " target="_blank"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="right">
                <h2>Tautan Fungsional</h2>
                <ul>
                    <li><a href="/CODINGAN/3-landingpageuser/beranda/beranda.php">Beranda</a></li>
                    <li>
                        <a href="/CODINGAN/3-landingpageuser/layanan/layanan.html">Layanan</a>
                    </li>
                    <li>
                        <a href="/CODINGAN/3-landingpageuser/galeri/galeri.php">Galeri</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            Copyright © 2024 Library of Riverhill Senior High School. All Rights Reserved
        </div>
    </footer>

</body>

</html>