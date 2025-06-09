<?php
session_start();
require_once 'formkoneksi.php';

$dokumen_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Cek apakah user sudah memberikan rating
$check_rating_query = "SELECT * FROM rating WHERE dokumen_id = ? AND anggota_id = ?";
$check_rating_stmt = $conn->prepare($check_rating_query);
$check_rating_stmt->execute([$dokumen_id, $user_id]);
$existing_rating = $check_rating_stmt->fetch(PDO::FETCH_ASSOC);

if ($existing_rating) {
    header("Location: index.php?id=$dokumen_id&error=Anda sudah memberikan rating untuk dokumen ini.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Ulasan</title>
    <link rel="stylesheet" href="tambah_ulasan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> 
</head>
<body>
    <h2>Tambah Ulasan untuk dokumen</h2>
    <form action="prorate.php" method="POST">
        <input type="hidden" name="dokumen_id" value="<?= $dokumen_id ?>">
        <input type="hidden" name="action" value="create">

        <label for="rating" requiered>Rating (1-5):</label>
<div id="rating-stars">
    <input type="radio" name="nilai" value="5" id="star5">
    <label for="star5" class="star">&#9733;</label>
    <input type="radio" name="nilai" value="4" id="star4">
    <label for="star4" class="star">&#9733;</label>
    <input type="radio" name="nilai" value="3" id="star3">
    <label for="star3" class="star">&#9733;</label>
    <input type="radio" name="nilai" value="2" id="star2">
    <label for="star2" class="star">&#9733;</label>
    <input type="radio" name="nilai" value="1" id="star1">
    <label for="star1" class="star">&#9733;</label>
</div>
        <!-- Ulasan -->
        <label for="ulasan">Ulasan:</label>
        <textarea name="ulasan" id="ulasan" rows="4" requiered></textarea>

        <!-- Tombol Kirim -->
        <button type="submit">Kirim Ulasan</button>
    </form>
</body>
</html>