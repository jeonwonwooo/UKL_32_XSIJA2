<?php
include 'formkoneksi.php'; // Koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';

    // ====================== LOGIN ADMIN =================================
    if ($type === 'admin_login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "Semua field harus diisi.";
            include 'formloginadm.php';
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && $admin['password'] === $password) {
                session_start();
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: /CODINGAN/4-landingpageadmin/3-landingpageuser/beranda/beranda.html");
                exit;
            } else {
                $error = "Username atau password salah.";
                include 'formloginadm.php';
                exit;
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
            include 'formloginadm.php';
            exit;
        }
    }

    // ====================== LOGIN USER ==================================
    elseif ($type === 'user_login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "Semua field harus diisi.";
            include 'formloginusr.php';
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['password'] === $password) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_username'] = $user['username'];
                header("Location: /CODINGAN/3-landingpageuser/beranda/beranda.html");
                exit;
            } else {
                $error = "Username atau password salah.";
                include 'formloginusr.php';
                exit;
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
            include 'formloginusr.php';
            exit;
        }
    }

    // ====================== REGISTRASI USER =============================
    elseif ($type === 'register') {
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($name) || empty($email) || empty($password)) {
            $error = "Semua field harus diisi.";
            include 'formregister.php';
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
                    include 'formregister.php';
                    exit;
                }
                if ($existing_user['email'] === $email) {
                    $error = "Email sudah terdaftar.";
                    include 'formregister.php';
                    exit;
                }
            }

            // Simpan data registrasi ke database
            $stmt = $conn->prepare("INSERT INTO anggota (username, password, nama, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $password, $name, $email]);

            // Simpan pesan sukses ke session
            session_start();
            $_SESSION['success'] = "Registrasi berhasil. Silakan login.";
            header("Location: formloginusr.php"); // Redirect ke halaman login
            exit;
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
            include 'formregister.php';
            exit;
        }
    }

    // ====================== DEFAULT REDIRECT ============================
    else {
        header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.html");
        exit;
    }
} else {
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.html");
    exit;
}
?>