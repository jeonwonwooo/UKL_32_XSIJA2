<?php
include 'formkoneksi.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID denda dari GET
$denda_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$denda_id) {
    die("ID DENDA TIDAK VALID");
}

try {
    // Ambil data denda lengkap dengan informasi peminjaman dan buku
    $query = "SELECT d.*, p.tanggal_pinjam, p.batas_pengembalian, b.judul, b.penulis, a.username, a.email
              FROM denda d
              JOIN peminjaman p ON d.peminjaman_id = p.id
              JOIN buku b ON p.buku_id = b.id
              JOIN anggota a ON d.anggota_id = a.id
              WHERE d.id = ? AND d.anggota_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$denda_id, $_SESSION['user_id']]);
    $denda = $stmt->fetch();

    if (!$denda) {
        $denda = false;
        throw new Exception("Data denda tidak ditemukan atau bukan milik Anda.");
    }

    // Jika metode POST, proses upload bukti pembayaran
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['metode_pembayaran'])) {
            throw new Exception("Metode pembayaran harus dipilih.");
        }

        // Periksa apakah file diupload dengan benar
        if (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Bukti pembayaran harus diunggah.");
        }

        // Validasi tipe file
        $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        $file_type = $_FILES['bukti_pembayaran']['type'];
        $file_extension = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
        
        // Cek tipe MIME dan ekstensi file
        if (!array_key_exists($file_type, $allowed_types)) {
            throw new Exception("Hanya menerima file JPG, PNG, atau PDF.");
        }
        if ($allowed_types[$file_type] !== $file_extension) {
            throw new Exception("Ekstensi file tidak sesuai dengan tipe file.");
        }

        // Validasi ukuran file (maksimal 2MB)
        $max_size = 2 * 1024 * 1024; // 2MB
        if ($_FILES['bukti_pembayaran']['size'] > $max_size) {
            throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
        }

        // Buat direktori upload jika belum ada
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/CODINGAN/4-landingpageadmin/uploads/";
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                throw new Exception("Gagal membuat direktori upload.");
            }
        }

        // Generate nama file unik
        $new_filename = 'bukti_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;
        $target_file = "{$target_dir}{$new_filename}";

        // Pindahkan file ke direktori target
        if (!move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_file)) {
            throw new Exception("Gagal menyimpan file. Error: " . $_FILES['bukti_pembayaran']['error']);
        }

        // Update data denda
        $update = $conn->prepare("
            UPDATE denda 
            SET bukti_pembayaran = ?, 
                metode_pembayaran = ?, 
                status = 'proses', 
                tanggal_pembayaran = NOW()
            WHERE id = ?
        ");
        $update->execute([
            $new_filename, // Simpan hanya nama file, bukan path lengkap
            $_POST['metode_pembayaran'],
            $denda_id
        ]);

        $_SESSION['success_message'] = "Bukti pembayaran berhasil diunggah. Silakan tunggu konfirmasi admin.";
        header("Location: ".$_SERVER['PHP_SELF']."?id=".$denda_id);
        exit;
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bayar Denda</title>
    <link rel="stylesheet" href="bayar_denda.css">
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?= $error_message ?>
            </div>
        <?php endif; ?>
        <?php if ($denda): ?>
        <div class="denda-info">
            <h2>Informasi Denda</h2>
            <table>
                <tr>
                    <th>Username</th>
                    <td><?= htmlspecialchars($denda['username']) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($denda['email']) ?></td>
                </tr>
                <tr>
                    <th>Judul Buku</th>
                    <td><?= htmlspecialchars($denda['judul']) ?></td>
                </tr>
                <tr>
                    <th>Penulis</th>
                    <td><?= htmlspecialchars($denda['penulis']) ?></td>
                </tr>
                <tr>
                    <th>Tanggal Pinjam</th>
                    <td><?= date('d/m/Y', strtotime($denda['tanggal_pinjam'])) ?></td>
                </tr>
                <tr>
                    <th>Batas Pengembalian</th>
                    <td><?= date('d/m/Y', strtotime($denda['batas_pengembalian'])) ?></td>
                </tr>
                <tr>
                    <th>Nominal Denda</th>
                    <td>Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td><?= htmlspecialchars($denda['keterangan']) ?></td>
                </tr>
            </table>
        </div>
        <?php endif; ?>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="denda_id" value="<?= htmlspecialchars($denda['id']) ?>">
            
            <div class="form-group metode-pembayaran">
                <label>Metode Pembayaran:</label>
                <div>
                    <label class="metode-option">
                        <input type="radio" name="metode_pembayaran" value="BCA" required> BCA
                    </label>
                    <label class="metode-option">
                        <input type="radio" name="metode_pembayaran" value="Mandiri"> Mandiri
                    </label>
                    <label class="metode-option">
                        <input type="radio" name="metode_pembayaran" value="BNI"> BNI
                    </label>
                    <label class="metode-option">
                        <input type="radio" name="metode_pembayaran" value="BRI"> BRI
                    </label>
                    <label class="metode-option">
                        <input type="radio" name="metode_pembayaran" value="QRIS"> QRIS
                    </label>
                    <label class="metode-option">
                        <input type="radio" name="metode_pembayaran" value="ShopeePay"> ShopeePay
                    </label>
                    <label class="metode-option">
                        <input type="radio" name="metode_pembayaran" value="Dana"> Dana
                    </label>
                    <label class="metode-option">
                        <input type="radio" name="metode_pembayaran" value="GoPay"> GoPay
                    </label>
                    <label class="metode-option">
                        <input type="radio" name="metode_pembayaran" value="OVO"> OVO
                    </label>
                </div>
            </div>

            <!-- Tutorial Pembayaran -->
            <div id="tutorial-bca" class="tutorial-pembayaran">
                <h3>Tutorial Pembayaran BCA</h3>
                <ol>
                    <li>Login ke aplikasi BCA Mobile</li>
                    <li>Pilih menu "Transfer"</li>
                    <li>Masukkan nomor rekening 1234567890 (Perpustakaan XYZ)</li>
                    <li>Masukkan nominal Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></li>
                    <li>Konfirmasi dan masukkan PIN</li>
                </ol>
            </div>

            <div id="tutorial-mandiri" class="tutorial-pembayaran">
                <h3>Tutorial Pembayaran Mandiri</h3>
                <ol>
                    <li>Login ke aplikasi Mandiri Mobile</li>
                    <li>Pilih menu "Transfer"</li>
                    <li>Masukkan nomor rekening 1234567890 (Perpustakaan XYZ)</li>
                    <li>Masukkan nominal Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></li>
                    <li>Konfirmasi dan masukkan PIN</li>
                </ol>
            </div>

            <div id="tutorial-bni" class="tutorial-pembayaran">
                <h3>Tutorial Pembayaran BNI</h3>
                <ol>
                    <li>Login ke aplikasi BNI Mobile</li>
                    <li>Pilih menu "Transfer"</li>
                    <li>Masukkan nomor rekening 1234567890 (Perpustakaan XYZ)</li>
                    <li>Masukkan nominal Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></li>
                    <li>Konfirmasi dan masukkan PIN</li>
                </ol>
            </div>

            <div id="tutorial-bri" class="tutorial-pembayaran">
                <h3>Tutorial Pembayaran BRI</h3>
                <ol>
                    <li>Login ke aplikasi BRI Mobile</li>
                    <li>Pilih menu "Transfer"</li>
                    <li>Masukkan nomor rekening 1234567890 (Perpustakaan XYZ)</li>
                    <li>Masukkan nominal Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></li>
                    <li>Konfirmasi dan masukkan PIN</li>
                </ol>
            </div>

            <div id="tutorial-qris" class="tutorial-pembayaran">
                <h3>Tutorial Pembayaran QRIS</h3>
                <ol>
                    <li>Buka aplikasi mobile banking atau e-wallet yang mendukung QRIS</li>
                    <li>Pilih menu "Scan QR Code"</li>
                    <li>Scan QR code berikut: <img src="qris_perpus.png" alt="QRIS Perpustakaan" width="100"></li>
                    <li>Masukkan nominal Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></li>
                    <li>Konfirmasi pembayaran</li>
                </ol>
            </div>

            <div id="tutorial-shopeepay" class="tutorial-pembayaran">
                <h3>Tutorial Pembayaran ShopeePay</h3>
                <ol>
                    <li>Buka aplikasi ShopeePay</li>
                    <li>Pilih menu "Pembayaran"</li>
                    <li>Masukkan kode pembayaran 1234567890 (Perpustakaan XYZ)</li>
                    <li>Masukkan nominal Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></li>
                    <li>Konfirmasi pembayaran</li>
                </ol>
            </div>

            <div id="tutorial-dana" class="tutorial-pembayaran">
                <h3>Tutorial Pembayaran Dana</h3>
                <ol>
                    <li>Buka aplikasi Dana</li>
                    <li>Pilih menu "Pembayaran"</li>
                    <li>Masukkan nomor virtual account 1234567890 (Perpustakaan XYZ)</li>
                    <li>Masukkan nominal Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></li>
                    <li>Konfirmasi pembayaran</li>
                </ol>
            </div>

            <div id="tutorial-gopay" class="tutorial-pembayaran">
                <h3>Tutorial Pembayaran GoPay</h3>
                <ol>
                    <li>Buka aplikasi GoPay</li>
                    <li>Pilih menu "Pembayaran"</li>
                    <li>Masukkan nomor virtual account 1234567890 (Perpustakaan XYZ)</li>
                    <li>Masukkan nominal Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></li>
                    <li>Konfirmasi pembayaran</li>
                </ol>
            </div>

            <div id="tutorial-ovo" class="tutorial-pembayaran">
                <h3>Tutorial Pembayaran OVO</h3>
                <ol>
                    <li>Buka aplikasi OVO</li>
                    <li>Pilih menu "Pembayaran"</li>
                    <li>Masukkan nomor virtual account 1234567890 (Perpustakaan XYZ)</li>
                    <li>Masukkan nominal Rp <?= number_format($denda['nominal'], 0, ',', '.') ?></li>
                    <li>Konfirmasi pembayaran</li>
                </ol>
            </div>

            <div class="form-group">
                <label for="file">Pilih File (JPG, JPEG, PNG, atau PDF)</label>
            <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required accept=".jpg,.jpeg,.png,.pdf">
            </div>
            <div class="form-group">
                <p>Pastikan Anda telah melakukan pembayaran sesuai dengan nominal denda yang tertera di atas.</p>
            </div>
            <button type="submit" class="btn btn-primary">Kirim Bukti Pembayaran</button>
        </form>
    </div>

    <script>
        // Menampilkan tutorial berdasarkan metode pembayaran yang dipilih
        document.querySelectorAll('input[name="metode_pembayaran"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Sembunyikan semua tutorial
                document.querySelectorAll('.tutorial-pembayaran').forEach(div => {
                    div.style.display = 'none';
                });
                
                // Tampilkan tutorial yang sesuai
                const selectedMethod = this.value.toLowerCase();
                const tutorialDiv = document.getElementById(`tutorial-${selectedMethod}`);
                if (tutorialDiv) {
                    tutorialDiv.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>