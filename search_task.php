<?php
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['username'] ?? 'User';
$results   = null;
$query_str = trim($_GET['q'] ?? '');

if ($query_str !== '') {
    $safe  = $conn->real_escape_string($query_str);
    $results = $conn->query("
        SELECT *, DATEDIFF(CURDATE(), due_date) AS days_overdue
        FROM tasks
        WHERE user_id = $user_id AND (title LIKE '%$safe%' OR description LIKE '%$safe%')
        ORDER BY due_date ASC
    ");
}

function priority_badge($p) {
    $map = ['High' => '#e74c3c', 'Medium' => '#f39c12', 'Low' => '#27ae60'];
    $color = $map[$p] ?? '#7f8c8d';
    return "<span class='badge' style='background:$color'>$p</span>";
}
function status_badge($s) {
    $map = ['Completed' => '#27ae60', 'In Progress' => '#2980b9', 'Pending' => '#7f8c8d'];
    $color = $map[$s] ?? '#7f8c8d';
    return "<span class='badge' style='background:$color'>$s</span>";
}

function highlight($text, $q) {
    if (!$q) return htmlspecialchars($text);
    $safe_q = preg_quote($q, '/');
    return preg_replace("/($safe_q)/i", "<mark>$1</mark>", htmlspecialchars($text));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Tasks | Student To-Do List</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme.js"></script>
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
            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon">🏠</span> Dashboard
            </a>
            <a href="view_task.php" class="nav-link">
                <span class="nav-icon">📋</span> My Tasks
            </a>
            <a href="create_task.php" class="nav-link">
                <span class="nav-icon">➕</span> Create Task
            </a>
            <a href="search_task.php" class="nav-link active">
                <span class="nav-icon">🔍</span> Search Tasks
            </a>
            <a href="filter_task.php" class="nav-link">
                <span class="nav-icon">⚙️</span> Filter Tasks
            </a>
        </nav>
        <a href="logout.php" class="sidebar-logout">🚪 Logout</a>
    </aside>

    <div class="main-content">

        <!-- Topbar -->
        <header class="topbar">
            <div>
                <h1 class="page-title">Search Tasks</h1>
                <p class="page-date"><?= date("l, d F Y") ?></p>
            </div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
                <div class="user-badge">👋 <strong><?= htmlspecialchars($username) ?></strong></div>
            </div>
        </header>

        <!-- Search Form -->
        <form method="GET" action="search_task.php" class="search-form">
            <div class="search-row">
                <input
                    type="text"
                    name="q"
                    class="search-input"
                    placeholder="Search by task title or description…"
                    value="<?= htmlspecialchars($query_str) ?>"
                    autofocus
                >
                <button type="submit" class="btn-primary">Search</button>
                <?php if ($query_str): ?>
                    <a href="search_task.php" class="btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($query_str !== ''): ?>
            <?php $count = $results ? $results->num_rows : 0; ?>
            <p class="result-meta">
                <?= $count ?> result<?= $count !== 1 ? 's' : '' ?> for
                <strong>"<?= htmlspecialchars($query_str) ?>"</strong>
            </p>

            <?php if ($count > 0): ?>
            <div class="table-wrap">
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Start Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $results->fetch_assoc()):
                        $days_over = intval($row['days_overdue']);
                        $row_class = ($days_over > 5 && $row['status'] !== 'Completed') ? 'row-critical' : (($days_over > 0 && $row['status'] !== 'Completed') ? 'row-overdue' : '');
                    ?>
                        <tr class="<?= $row_class ?>">
                            <td class="task-title">
                                <?= highlight($row['title'], $query_str) ?>
                                <?php if ($days_over > 5 && $row['status'] !== 'Completed'): ?>
                                    <span class="overdue-tag">🔴 <?= $days_over ?>d overdue</span>
                                <?php elseif ($days_over > 0 && $row['status'] !== 'Completed'): ?>
                                    <span class="overdue-tag-mild">⚠️ <?= $days_over ?>d overdue</span>
                                <?php endif; ?>
                                <?php if (!empty($row['description'])): ?>
                                    <div style="font-size: 0.8em; color: var(--muted); font-weight: normal; margin-top: 3px;">
                                        <?= highlight(mb_substr($row['description'], 0, 70), $query_str) . (mb_strlen($row['description']) > 70 ? '…' : '') ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="cat-tag"><?= htmlspecialchars($row['category']) ?></span></td>
                            <td><?= priority_badge($row['priority']) ?></td>
                            <td class="date-cell">
                                <?= isset($row['created_at']) ? date("d M Y", strtotime($row['created_at'])) : '—' ?>
                            </td>
                            <td class="date-cell <?= ($days_over > 5 && $row['status'] !== 'Completed') ? 'date-critical' : (($days_over > 0 && $row['status'] !== 'Completed') ? 'date-overdue' : '') ?>">
                                <?= !empty($row['due_date']) ? date("d M Y", strtotime($row['due_date'])) : '—' ?>
                            </td>
                            <td><?= status_badge($row['status']) ?></td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="update_task.php?id=<?= $row['task_id'] ?>" style="background: #e8edff; color: var(--accent); padding: 4px 8px; border-radius: 6px; font-size: 0.8em; font-weight:600;">✏️ Edit</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>🔍 No tasks found matching "<strong><?= htmlspecialchars($query_str) ?></strong>".</p>
                    <p>Try a different keyword or <a href="view_task.php">view all tasks</a>.</p>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <p>Type a task title or description above to search.</p>
            </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
