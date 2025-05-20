<?php
session_start();
require_once 'formkoneksi.php';

$buku_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Cek apakah user sudah memberikan rating
$check_rating_query = "SELECT * FROM rating WHERE buku_id = ? AND anggota_id = ?";
$check_rating_stmt = $conn->prepare($check_rating_query);
$check_rating_stmt->execute([$buku_id, $user_id]);
$existing_rating = $check_rating_stmt->fetch(PDO::FETCH_ASSOC);

if ($existing_rating) {
    header("Location: index.php?id=$buku_id&error=Anda sudah memberikan rating untuk buku ini.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Ulasan</title>
</head>
<body>
    <h2>Tambah Ulasan untuk Buku</h2>
    <form action="proses_rating.php" method="POST">
        <input type="hidden" name="buku_id" value="<?= $buku_id ?>">
        <input type="hidden" name="action" value="create">
        <label for="rating">Berikan Rating:</label>
        <select name="nilai" id="rating">
            <option type= "radio" value="1" required>1</option>
            <option type= "radio" value="2" required>2</option>
            <option type= "radio" value="3" required>3</option>
            <option type= "radio" value="4" required>4</option>
            <option type= "radio" value="5" required>5</option>
        </select>
        <br>
        <label for="ulasan">Ulasan:</label>
        <textarea name="ulasan" id="ulasan" rows="4"></textarea>
        <br>
        <button type="submit">Kirim Ulasan</button>
    </form>
</body>
</html>