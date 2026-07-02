<?php
// Ensure config is included and session is active
if (!isset($_SESSION)) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'User';
$currentDate = date("l, d F Y");

// Set active page if not defined
if (!isset($page_active)) {
    $page_active = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : "Dashboard"; ?> | Student To-Do List</title>
    <meta name="description" content="<?php echo isset($page_desc) ? htmlspecialchars($page_desc) : 'Student To-Do List - Manage your academic tasks efficiently.'; ?>">
    <link rel="stylesheet" href="assets/css/style.css?v=2">
    <script src="assets/js/theme.js"></script>
    <?php if (isset($extra_styles)) echo $extra_styles; ?>
</head>
<body>
<div class="layout">

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-icon">📚</span>
            <span class="logo-text">To-Do List</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link <?php echo ($page_active === 'dashboard') ? 'active' : ''; ?>">
                <span class="nav-icon">🏠</span> Dashboard
            </a>
            <a href="view_task.php" class="nav-link <?php echo ($page_active === 'view_tasks') ? 'active' : ''; ?>">
                <span class="nav-icon">📋</span> My Tasks
            </a>
            <a href="create_task.php" class="nav-link <?php echo ($page_active === 'create_task') ? 'active' : ''; ?>">
                <span class="nav-icon">➕</span> Create Task
            </a>
            <a href="search_task.php" class="nav-link <?php echo ($page_active === 'search_tasks') ? 'active' : ''; ?>">
                <span class="nav-icon">🔍</span> Search Tasks
            </a>
            <a href="filter_task.php" class="nav-link <?php echo ($page_active === 'filter_tasks') ? 'active' : ''; ?>">
                <span class="nav-icon">⚙️</span> Filter Tasks
            </a>
            <a href="profile.php" class="nav-link <?php echo ($page_active === 'profile') ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span> Profile
            </a>
            <a href="about.php" class="nav-link <?php echo ($page_active === 'about') ? 'active' : ''; ?>">
                <span class="nav-icon">ℹ️</span> About
            </a>
        </nav>
        <a href="logout.php" class="sidebar-logout">🚪 Logout</a>
    </aside>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Topbar -->
        <header class="topbar">
            <div>
                <h1 class="page-title"><?php echo isset($page_title) ? htmlspecialchars($page_title) : "Dashboard"; ?></h1>
                <p class="page-date"><?= $currentDate ?></p>
            </div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
                <div class="user-badge">👋 Hello, <strong><?= htmlspecialchars($username) ?></strong></div>
            </div>
        </header>
