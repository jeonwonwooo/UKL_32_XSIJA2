<?php
session_start();
include 'formkoneksi.php'; // Pastikan file ini ada & koneksi ke database benar

if (isset($_POST["submit"])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah username ada di database
    $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Cek apakah password sesuai (karena tidak di-hash)
        if ($password === $user['password']) {
            $_SESSION['username'] = $username; // Simpan session
            header("Location: /3-landingpageuser/index.php
            "); // Redirect ke landing page
            exit;
        } else {
            header("Location: formloginusr.html?error=Password salah.");
            exit;
        }
    } else {
        header("Location: formloginusr.html?error=Username tidak ditemukan.");
        exit;
    }
}
?>
