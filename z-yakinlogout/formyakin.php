<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Logout Confirmation</title>
    <link rel="stylesheet" href="formyakin.css" />
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
</head>
<body>
    <div class="container">
        <div class="overlay"></div>
        <div class="content">
            <h1>Yakin Logout?</h1>
            <p>Tekan tombol di bawah untuk memastikan ulang!</p>
            <div class="buttons">
                <form action="/CODINGAN/z-yakinlogout/proses_logout.php" method="POST">
                <button type="submit" name="konfirmasi" value="ya" class="btn yes">Ya</button>
            </form>
                <form action="/CODINGAN/z-yakinlogout/proses_cancel.php" method="POST">
                <button type="submit" class="btn no">Tidak</button>
            </form>
            </div>
        </div>
    </div>
</body>
</html>