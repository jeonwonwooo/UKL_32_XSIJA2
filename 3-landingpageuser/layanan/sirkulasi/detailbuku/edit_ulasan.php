<?php
session_start();
require_once 'formkoneksi.php';

// Ambil ID ulasan dari URL
$ulasan_id = isset($_GET['id']) ? $_GET['id'] : null;

// Jika tidak ada ulasan_id, redirect ke halaman lain
if (!$ulasan_id) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek apakah ulasan ini milik pengguna yang sedang login
$check_query = "SELECT * FROM rating WHERE id = ? AND anggota_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->execute([$ulasan_id, $user_id]);
$existing_review = $check_stmt->fetch(PDO::FETCH_ASSOC);

if (!$existing_review) {
    // Jika ulasan tidak ditemukan atau bukan milik pengguna
    header("Location: index.php?error=Anda tidak memiliki akses untuk mengedit ulasan ini.");
    exit;
}

$buku_id = $existing_review['buku_id'];
$current_rating = $existing_review['nilai'];
$current_comment = $existing_review['ulasan'];

// Proses form edit ulasan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if 'nilai' is set in $_POST
    $new_rating = isset($_POST['nilai']) ? (int)$_POST['nilai'] : null;
    $new_comment = trim($_POST['ulasan']);

    if ($new_rating >= 1 && $new_rating <= 5 && !empty($new_comment)) {
        // Update ulasan di database
        $update_query = "UPDATE rating SET nilai = ?, ulasan = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([$new_rating, $new_comment, $ulasan_id]);

        // Redirect kembali ke halaman detail buku
        header("Location: detail_buku.php?id=$buku_id");
        exit;
    } else {
        $error_message = "Rating harus antara 1-5 dan ulasan tidak boleh kosong.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ulasan</title>
    <link rel="stylesheet" href="edit_ulasan.css">
</head>
<body>
    <h2>Edit Ulasan untuk Buku</h2>

    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="buku_id" value="<?= $buku_id ?>">
        <input type="hidden" name="action" value="update">

        <label for="rating">Rating (1-5):</label>
        <div id="rating-stars">
            <?php
            // Pre-select the current rating
            for ($i = 5; $i >= 1; $i--) {
                $checked = ($i == $current_rating) ? 'checked' : '';
                echo '<input type="radio" name="nilai" value="' . $i . '" id="star' . $i . '" ' . $checked . '>';
                echo '<label for="star' . $i . '" class="star">&#9733;</label>';
            }
            ?>
        </div>

        <label for="comment">Komentar:</label>
        <textarea id="comment" name="ulasan" rows="5" required><?= htmlspecialchars($current_comment) ?></textarea>

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>