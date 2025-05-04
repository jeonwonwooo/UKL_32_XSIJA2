<?php
session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <div class="admin-panel">
        <aside class="sidebar">
            <div class="logo">
                <h2>Admin Panel</h2>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href=".php">List Pengguna</a></li>
                    <li><a href=".php">List Admin</a></li>
                    <li><a href=".php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="topbar">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
            </header>
            <section class="dashboard-content">
                <h2>Dashboard</h2>
                <p>This is your admin dashboard. You can manage users and other resources from here.</p>
            </section>
        </main>
    </div>
</body>

</html>