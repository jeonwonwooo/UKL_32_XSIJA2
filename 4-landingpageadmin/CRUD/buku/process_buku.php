<?php include 'includes/formkoneksi.php'; ?>

<?php
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'delete' && $id) {
    try {
        // Ambil data gambar dari database
        $stmt = $conn->prepare("SELECT gambar FROM buku WHERE id = ?");
        $stmt->execute([$id]);
        $buku = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($buku) {
            // Hapus gambar dari folder uploads jika ada
            $upload_dir = "../../uploads/";
            if ($buku['gambar'] && file_exists($upload_dir . $buku['gambar'])) {
                unlink($upload_dir . $buku['gambar']);
            }

            // Hapus data buku dari database
            $stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
            $stmt->execute([$id]);
        }

        header("Location: buku_list.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("Invalid action.");
}
?>