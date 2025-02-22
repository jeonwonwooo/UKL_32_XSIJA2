<?php
include 'formkoneksi.php'; // Koneksi database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($username) || empty($password)) {
        header("Location: formloginusr.html?error=Semua field harus diisi.");
        exit;
    }

    try {
        // Cari user berdasarkan username
        $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil
            session_start(); // Mulai session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_nama'] = $user['nama'];

            // Redirect ke halaman dashboard user
            header("Location: /3-landingpageuser/index.html"); // Ganti dengan halaman user
            exit;
        } else {
            // Login gagal
            header("Location: formloginusr.html?error=Username atau password salah.");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: formloginusr.html?error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Jika bukan POST request, redirect ke halaman login
    header("Location: formloginusr.html");
    exit;
}
?>