<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="1-brflogin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css " rel="stylesheet">
</head>
<body>
    <?php
    // Check for status messages
    $status = $_GET['status'] ?? '';
    if ($status === 'logout_sukses') {
        echo '<div class="notification success">Anda berhasil logout.</div>';
    } elseif ($status === 'error_logout') {
        echo '<div class="notification error">Terjadi kesalahan saat logout. Silakan coba lagi.</div>';
    } elseif ($status === 'error_role') {
        echo '<div class="notification error">Role tidak dikenali. Silakan login kembali.</div>';
    }
    ?>
    <!-- Login form -->
    <div class="container">
        <div class="overlay"></div>
        <div class="content">
            <h1>Hello, <span class="highlight">Love!</span></h1>
            <p>Selamat datang pada laman ini, silakan login/registrasi terlebih dahulu!</p>
            <div class="buttons">
                <a href="/CODINGAN/2-loginregis/formloginadm.php" class="btn admin"><i class="fas fa-user"></i> Admin</a>
                <a href="/CODINGAN/2-loginregis/formloginusr.php" class="btn user"><i class="fas fa-users"></i> User</a>
            </div>
        </div>
    </div>
</body>
</html>