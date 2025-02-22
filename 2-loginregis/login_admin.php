<?php
include 'formkoneksi.php'; // Koneksi database

// Fungsi untuk mengarahkan ke halaman tertentu dengan pesan error
function redirectWithError($error) {
    header("Location: formloginadm.html?error=" . urlencode($error));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan input dari form
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($username) || empty($password)) {
        // Jika ada field yang kosong
        redirectWithError('Semua field harus diisi.');
    }

    try {
        // Cari admin berdasarkan username
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Jika username ditemukan, cek password
            if (password_verify($password, $admin['password'])) {
                // Login berhasil
                session_start(); // Mulai session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama'];

                // Redirect ke halaman dashboard admin
                header("Location: /3-landingpageuser/index.html"); // Ganti dengan halaman admin
                exit;
            } else {
                // Password salah
                redirectWithError('Username atau password salah.');
            }
        } else {
            // Username tidak ditemukan di database
            redirectWithError('Data tidak tercatat.');
        }
    } catch (PDOException $e) {
        // Tangani error database
        redirectWithError($e->getMessage());
    }
} else {
    // Jika bukan POST request, redirect ke halaman login
    header("Location: formloginadm.html");
    exit;
}
?>