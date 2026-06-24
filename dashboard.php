<?php
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$currentDate = date("l, d F Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Student To-Do List</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="dashboard-page">

<!-- Sidebar -->
<div class="sidebar">
    <h2 class="logo">📚 To-Do List</h2>

    <a href="dashboard.php" class="active">🏠 Dashboard</a>
    <a href="index.php">📝 Tasks</a>
    <a href="#">👤 Profile</a>
    <a href="logout.php" class="logout-link">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">

    <!-- Top Bar -->
    <div class="topbar">
        <div>
            <h1>Dashboard</h1>
            <p><?php echo $currentDate; ?></p>
        </div>

        <div class="user-badge">
            👋 Hello, <strong><?php echo htmlspecialchars($username); ?></strong>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="welcome-card">
        <h2>Welcome Back, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>
            Manage your tasks, track your progress, and stay productive with
            the Student To-Do List Web Application.
        </p>
    </div>

    <!-- Information Cards -->
    <div class="card-container">

        <div class="info-card">
            <h3>📅 Current Date</h3>
            <p><?php echo $currentDate; ?></p>
        </div>

        <div class="info-card">
            <h3>👤 User Information</h3>
            <p>Username: <strong><?php echo htmlspecialchars($username); ?></strong></p>
        </div>

        <div class="info-card">
            <h3>🎯 Welcome Message</h3>
            <p>Ready to organize your day and complete your tasks?</p>
        </div>

    </div>

</div>

</body>
</html>
```
