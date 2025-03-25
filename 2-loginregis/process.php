<?php
include 'formkoneksi.php';

// Mulai session di awal skrip
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';

    // Login Admin
    if ($type === 'admin_login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Semua field harus diisi.";
            header("Location: formloginadm.php");
            exit;
        }

        try {
            // Cari admin berdasarkan username
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Validasi password
            if ($admin && $admin['password'] === $password) {
                // Simpan session admin
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['role'] = 'admin';
                header("Location: /CODINGAN/4-landingpageadmin/landingpage/beranda/beranda.html");
                exit;
            } else {
                $_SESSION['error'] = "Username atau password salah.";
                header("Location: formloginadm.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
            header("Location: formloginadm.php");
            exit;
        }
    }

    // Login User
    elseif ($type === 'user_login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Semua field harus diisi.";
            header("Location: formloginusr.php");
            exit;
        }

        try {
            // Cari user berdasarkan username
            $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Validasi password
            if ($user && $user['password'] === $password) {
                // Simpan session user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['role'] = 'user';
                header("Location: /CODINGAN/3-landingpageuser/beranda/beranda.html");
                exit;
            } else {
                $_SESSION['error'] = "Username atau password salah.";
                header("Location: formloginusr.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
            header("Location: formloginusr.php");
            exit;
        }
    }

    // Register User
    elseif ($type === 'register') {
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validasi input
        if (empty($username) || empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Semua field harus diisi.";
            header("Location: formregister.php");
            exit;
        }

        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Format email tidak valid.";
            header("Location: formregister.php");
            exit;
        }

        try {
            // Cek apakah username atau email sudah ada
            $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_user) {
                if ($existing_user['username'] === $username) {
                    $_SESSION['error'] = "Username sudah digunakan.";
                    header("Location: formregister.php");
                    exit;
                }
                if ($existing_user['email'] === $email) {
                    $_SESSION['error'] = "Email sudah terdaftar.";
                    header("Location: formregister.php");
                    exit;
                }
            }

            // Simpan data user baru
            $stmt = $conn->prepare("INSERT INTO anggota (username, password, nama, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $password, $name, $email]);

            $_SESSION['success'] = "Registrasi berhasil. Silakan login.";
            header("Location: formloginusr.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
            header("Location: formregister.php");
            exit;
        }
    }

    // Default redirect jika type tidak dikenali
    else {
        header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.html");
        exit;
    }
} else {
    // Redirect jika akses bukan POST
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.html");
    exit;
}
?>