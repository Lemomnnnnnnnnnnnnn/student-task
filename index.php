<?php include "includes/config.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Home | Student To-Do List</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="home-page">

<div class="home-container">
    <div class="home-card">
        <div class="logo">📝</div>
        <h1>Student To-Do List</h1>

        <div class="btn-group">
            <?php if (isset($_SESSION['user_id'])) { ?>
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="logout.php" class="btn logout">Logout</a>
            <?php } else { ?>
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn register">Register</a>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>