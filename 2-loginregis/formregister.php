<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="regisz.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <div class="wrapper">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert" style="position: absolute; top: -60px; left: 50%; transform: translateX(-50%); width: 100%; max-width: 420px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success" role="alert" style="position: absolute; top: -60px; left: 50%; transform: translateX(-50%); width: 100%; max-width: 420px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form action="process.php" method="POST">
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
                <label><input type="checkbox" name="terms" required> I agree to the <a href="#">terms & conditions</a></label>
            </div>
            <button type="submit" class="button" name="submit">Register</button>
            <div class="register-link">
                <p>Already have an account? <a href="formloginusr.php">Login</a></p>
            </div>
        </form>
    </div>
</body>
</html>