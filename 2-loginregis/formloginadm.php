<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="loginz.css">
    <link rel="icon" type="image/x-icon" href="/CODINGAN/assets/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    session_start();
    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    ?>
    <?php if (isset($success)): ?>
        <div style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); width: 100%; max-width: 420px; background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); width: 100%; max-width: 420px; background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <div class="wrapper">
        <form action="process_login.php" method="POST">
            <input type="hidden" name="type" value="admin_login">
            <h1>Login Admin</h1>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <div class="remember-forgot">
    <label>
        <input type="checkbox" name="remember_me" value="1">
        Remember Me
    </label>
</div>
            <button type="submit" class="button">Login</button>
        </form>
    </div>
</body>

</html>