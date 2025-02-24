<?php
include 'formkoneksi.php'; // Koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';

    // ====================================================================
    // ====================== LOGIN ADMIN =================================
    // ====================================================================
    if ($type === 'admin_login') {
        // Ambil data dari form login admin
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input
        if (empty($username) || empty($password)) {
            $error = "Semua field harus diisi.";
            include 'formloginadm.php'; // Tampilkan halaman login admin dengan pesan error
            exit;
        }

        try {
            // Cari admin berdasarkan username
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifikasi password
            if ($admin && $admin['password'] === $password) {
                session_start();
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                // Redirect ke dashboard admin jika berhasil
                header("Location: /CODINGAN/4-landingpageadmin/beranda/beranda.html");
                exit;
            } else {
                $error = "Username atau password salah.";
                include 'formloginadm.php'; // Tampilkan halaman login admin dengan pesan error
                exit;
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
            include 'formloginadm.php'; // Tampilkan halaman login admin dengan pesan error
            exit;
        }
    }

    // ====================================================================
    // ====================== LOGIN USER ==================================
    // ====================================================================
    elseif ($type === 'user_login') {
        // Ambil data dari form login user
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input
        if (empty($username) || empty($password)) {
            $error = "Semua field harus diisi.";
            include 'formloginusr.php'; // Tampilkan halaman login user dengan pesan error
            exit;
        }

        try {
            // Cari user berdasarkan username
            $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifikasi password
            if ($user && $user['password'] === $password) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_username'] = $user['username'];

                // Redirect ke dashboard user jika berhasil
                header("Location: /CODINGAN/3-landingpageuser/beranda/beranda.html");
                exit;
            } else {
                $error = "Username atau password salah.";
                include 'formloginusr.php'; // Tampilkan halaman login user dengan pesan error
                exit;
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
            include 'formloginusr.php'; // Tampilkan halaman login user dengan pesan error
            exit;
        }
    }

    // ====================================================================
    // ====================== REGISTRASI USER =============================
    // ====================================================================
    elseif ($type === 'register') {
        // Ambil data dari form registrasi
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input
        if (empty($username) || empty($name) || empty($email) || empty($password)) {
            $error = "Semua field harus diisi.";
            include 'formregister.php'; // Tampilkan halaman registrasi dengan pesan error
            exit;
        }

        try {
            // Cek apakah username atau email sudah ada
            $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existing_user = $stmt->fetch();

            if ($existing_user) {
                if ($existing_user['username'] === $username) {
                    $error = "Username sudah digunakan.";
                    include 'formregister.php'; // Tampilkan halaman registrasi dengan pesan error
                    exit;
                }
                if ($existing_user['email'] === $email) {
                    $error = "Email sudah terdaftar.";
                    include 'formregister.php'; // Tampilkan halaman registrasi dengan pesan error
                    exit;
                }
            }

            // Simpan password sebagai plaintext (tanpa hashing)
            $stmt = $conn->prepare("INSERT INTO anggota (username, password, nama, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $password, $name, $email]);

            $success = "Registrasi berhasil. Silakan login.";
            include 'formregister.php'; // Tampilkan halaman registrasi dengan pesan sukses
            exit;
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
            include 'formregister.php'; // Tampilkan halaman registrasi dengan pesan error
            exit;
        }
    }

    // ====================================================================
    // ====================== DEFAULT REDIRECT ============================
    // ====================================================================
    else {
        // Jika tipe form tidak valid
        header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.html");
        exit;
    }
} else {
    // Jika bukan POST request, redirect ke halaman utama
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.html");
    exit;
}
?>