<?php
include 'formkoneksi.php'; // Koneksi database

if (isset($_POST["submit"])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // Password tidak di-hash
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Cek apakah user menyetujui syarat & ketentuan
    if (!isset($_POST['terms'])) {
        header("Location: register.html?error=Harap setujui syarat & ketentuan.");
        exit;
    }

    // Cek apakah ada field kosong
    if (empty($username) || empty($password) || empty($name) || empty($email)) {
        header("Location: register.html?error=Semua field harus diisi.");
        exit;
    }

    try {
        // Cek apakah username sudah digunakan
        $stmt = $conn->prepare("SELECT * FROM anggota WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            header("Location: register.html?error=Username sudah digunakan.");
            exit;
        }

        // Insert data ke database tanpa hashing password
        $sql = "INSERT INTO anggota (username, password, nama, email) VALUES (:username, :password, :name, :email)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password); // Password disimpan langsung
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);

        if ($stmt->execute()) {
            header("Location: formloginusr.html");
            exit;
        } else {
            header("Location: register.html?error=Gagal mendaftar. Coba lagi.");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: register.html?error=" . urlencode($e->getMessage()));
        exit;
    }
}
?>