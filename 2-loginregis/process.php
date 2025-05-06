<?php
session_start();

include 'formkoneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';

    // Login Admin
    if ($type === 'admin_login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Semua field harus diisi.";
            header("Location: /CODINGAN/2-loginregis/formloginadm.php");
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
            $stmt->execute([$username, $password]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['role'] = 'admin';

                // Redirect ke dashboard admin
                header("Location: /CODINGAN/4-landingpageadmin/landingpage/dashboard.php");
                exit;
            } else {
                $_SESSION['error'] = "Username atau password salah.";
                header("Location: /CODINGAN/2-loginregis/formloginadm.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
            header("Location: /CODINGAN/2-loginregis/formloginadm.php");
            exit;
        }
    }

    // Login User
    elseif ($type === 'user_login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Semua field harus diisi.";
            header("Location: /CODINGAN/2-loginregis/formloginusr.php");
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = ? AND password = ?");
            $stmt->execute([$username, $password]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['role'] = 'user';

                // Redirect ke halaman beranda user
                header("Location: /CODINGAN/3-landingpageuser/beranda/beranda.html");
                exit;
            } else {
                $_SESSION['error'] = "Username atau password salah.";
                header("Location: /CODINGAN/2-loginregis/formloginusr.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
            header("Location: /CODINGAN/2-loginregis/formloginusr.php");
            exit;
        }
    }

    // Register User
    elseif ($type === 'register') {
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Semua field harus diisi.";
            header("Location: /CODINGAN/2-loginregis/formregister.php");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Format email tidak valid.";
            header("Location: /CODINGAN/2-loginregis/formregister.php");
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_user) {
                if ($existing_user['username'] === $username) {
                    $_SESSION['error'] = "Username sudah digunakan.";
                    header("Location: /CODINGAN/2-loginregis/formregister.php");
                    exit;
                }
                if ($existing_user['email'] === $email) {
                    $_SESSION['error'] = "Email sudah terdaftar.";
                    header("Location: /CODINGAN/2-loginregis/formregister.php");
                    exit;
                }
            }

            $stmt = $conn->prepare("INSERT INTO anggota (username, password, nama, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $password, $name, $email]);

            $_SESSION['success'] = "Registrasi berhasil. Silakan login.";
            header("Location: /CODINGAN/2-loginregis/formloginusr.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
            header("Location: /CODINGAN/2-loginregis/formregister.php");
            exit;
        }
    }

    // Default redirect jika type tidak dikenali
    else {
        header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.html");
        exit;
    }
} else {
    header("Location: /CODINGAN/1-pagebeforelogin/1-beforelogin.html");
    exit;
}
