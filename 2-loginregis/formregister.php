<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="regisz.css">
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    session_start();
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
    }
    ?>

    <?php if (isset($error)): ?>
        <div style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 420px; background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center; z-index: 1000;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 420px; background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center; z-index: 1000;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="wrapper">
        <form action="process_login.php" method="POST">
            <input type="hidden" name="type" value="register">
            <h1>Register</h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="text" name="name" placeholder="Name" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class='bx bxs-envelope'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <div class="remember-forgot">
    <label>
        <input type="checkbox" name="terms" required>
        I agree to the <a href="#" onclick="openModal(); return false;">terms & conditions</a>
    </label>
</div>
            <button type="submit" class="button" name="submit">Register</button>
            <div class="register-link">
                <p>Already have an account? <a href="formloginusr.php">Login</a></p>
            </div>
        </form>
    </div>
<!-- Modal Terms & Conditions -->
<div id="tncModal" class="modal-tc">
  <div class="modal-content-tc">
    <span onclick="closeModal()" style="position: absolute; top: 10px; right: 20px; font-size: 1.5rem; cursor: pointer;">&times;</span>
    <h2>Terms & Conditions</h2>
    <p>Welcome to the Library of Riverhill Senior High School! By using our library services, you agree to comply with and be bound by the following terms and conditions:</p>

    <h2>Introduction</h2>
    <p>Welcome to the Library of Riverhill Senior High School! By using our library services, you agree to comply with and be bound by the following terms and conditions:</p>

    <h2>1. Acceptance of Terms</h2>
    <p>By accessing or using our library platform, you agree to abide by these terms and conditions. If you do not agree with any part of these terms, please refrain from using our services.</p>

    <h2>2. User Conduct</h2>
    <p>You agree to use our library services responsibly and in accordance with all applicable laws and regulations. You are responsible for maintaining the confidentiality of your account credentials and for all activities conducted under your account.</p>

    <h2>3. Intellectual Property</h2>
    <p>All materials provided on this platform, including books, articles, images, and other content, are protected by intellectual property laws. Unauthorized reproduction, distribution, or modification of any content is strictly prohibited without prior written permission from the library.</p>

    <h2>4. Privacy Policy</h2>
    <p>We respect your privacy. We collect and use your personal information in accordance with our Privacy Policy. By using our services, you consent to the collection, use, and protect your personal information.</p>

    <h2>5. Borrowing Policies</h2>
    <p>Users must adhere to the borrowing policies set by the library. This includes returning borrowed items on time and paying any applicable fines for late returns or damaged items. Failure to comply may result in suspension of borrowing privileges.</p>

    <h2>6. Limitation of Liability</h2>
    <p>The library shall not be liable for any damages arising from the use of its services, including but not limited to loss of data, interruptions in service, or errors in content. Use of our platform is at your own risk.</p>

    <h2>7. Governing Law</h2>
    <p>These terms and conditions are governed by the laws of the jurisdiction in which the library is located. Any disputes will be resolved through arbitration in accordance with applicable legal procedures.</p>

    <h2>8. Contact Us</h2>
    <p>If you have any questions about these terms, please contact us at 
        <a href="mailto:syarivatunnisai@gmail.com" target="_blank">syarivatunnisai@gmail.com</a>.
    </p>
  </div>
  <div class="modal-footer-tc" style="text-align: center; margin-top: 2rem;">
      <button onclick="closeModal()">Close</button>
    </div>
</div>
    <script>
function openModal() {
    document.getElementById("tncModal").style.display = "block";
}
function closeModal() {
    document.getElementById("tncModal").style.display = "none";
}
window.onclick = function(event) {
    const modal = document.getElementById("tncModal");
    if (event.target == modal) {
        closeModal();
    }
}
</script>

</body>

</html>