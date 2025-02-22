<?php
include 'formkoneksi.php'; // Koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($username) || empty($password)) {
        header("Location: formloginadm.html?error=Semua field harus diisi.");
        exit;
    }

    try {
        // Cari admin berdasarkan username
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            // Login berhasil
            session_start(); // Mulai session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama'];

            // Redirect ke halaman dashboard admin
            header("Location: /3-landingpageuser/index.html"); // Ganti dengan halaman admin
            exit;
        } else {
            // Login gagal
            header("Location: formloginadm.html?error=Username atau password salah.");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: formloginadm.html?error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Jika bukan POST request, redirect ke halaman login
    header("Location: formloginadm.html");
    exit;
}
?>