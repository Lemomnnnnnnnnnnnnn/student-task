<?php
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ── Filters & Search ──────────────────────────────────────────
$filter_status   = $_GET['status']   ?? 'all';
$filter_priority = $_GET['priority'] ?? 'all';
$filter_category = $_GET['category'] ?? 'all';
$search_query    = trim($_GET['search'] ?? '');

// ── Handle delete ──────────────────────────────────────────────
$delete_msg = "";
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int) $_GET['delete'];
    $stmt   = $conn->prepare("DELETE FROM tasks WHERE task_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $del_id, $user_id);
    if ($stmt->execute()) {
        $delete_msg = "Task deleted successfully.";
    }
    $stmt->close();
}

// ── Build query ────────────────────────────────────────────────
$where  = ["user_id = ?"];
$params = [$user_id];
$types  = "i";

if ($filter_status !== 'all') {
    $where[]  = "status = ?";
    $params[] = $filter_status;
    $types   .= "s";
}

if ($filter_priority !== 'all') {
    $where[]  = "priority = ?";
    $params[] = $filter_priority;
    $types   .= "s";
}

if ($filter_category !== 'all') {
    $where[]  = "category = ?";
    $params[] = $filter_category;
    $types   .= "s";
}

if (!empty($search_query)) {
    $where[]  = "(title LIKE ? OR description LIKE ?)";
    $like     = "%$search_query%";
    $params[] = $like;
    $params[] = $like;
    $types   .= "ss";
}

$where_sql = implode(" AND ", $where);
$sql       = "SELECT *, DATEDIFF(CURDATE(), due_date) AS days_overdue 
              FROM tasks 
              WHERE $where_sql 
              ORDER BY FIELD(priority,'High','Medium','Low'), due_date ASC, created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Summary counts ─────────────────────────────────────────────
$count_stmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total,
        SUM(status='Pending')     AS pending,
        SUM(status='In Progress') AS inprogress,
        SUM(status='Completed')   AS completed
     FROM tasks WHERE user_id = ?"
);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$counts = $count_stmt->get_result()->fetch_assoc();
$count_stmt->close();

$username    = $_SESSION['username'] ?? 'User';
$currentDate = date("l, d F Y");

// ── Helpers ─────────────────────────────────────────────────────
function priorityBadge(string $p): string {
    $map = [
        'High'   => ['bg'=>'var(--danger)','color'=>'#fff','icon'=>'🔴'],
        'Medium' => ['bg'=>'var(--warn)','color'=>'#fff','icon'=>'🟡'],
        'Low'    => ['bg'=>'var(--success)','color'=>'#fff','icon'=>'🟢'],
    ];
    $s = $map[$p] ?? $map['Low'];
    return "<span class=\"badge\" style=\"background:{$s['bg']};color:{$s['color']}\">{$s['icon']} $p</span>";
}

function statusBadge(string $s): string {
    $map = [
        'Pending'     => ['bg'=>'#7f8c8d','color'=>'#fff','icon'=>'⏳'],
        'In Progress' => ['bg'=>'#2980b9','color'=>'#fff','icon'=>'🔄'],
        'Completed'   => ['bg'=>'#27ae60','color'=>'#fff','icon'=>'✅'],
    ];
    $d = $map[$s] ?? $map['Pending'];
    return "<span class=\"badge\" style=\"background:{$d['bg']};color:{$d['color']}\">{$d['icon']} $s</span>";
}

function isDueSoon(?string $due): bool {
    if (!$due) return false;
    $diff = (strtotime($due) - strtotime(date('Y-m-d'))) / 86400;
    return $diff >= 0 && $diff <= 2;
}

function isOverdue(?string $due, string $status): bool {
    if (!$due || $status === 'Completed') return false;
    return strtotime($due) < strtotime(date('Y-m-d'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks | Student To-Do List</title>
    <meta name="description" content="View, search and filter all your tasks in one premium dashboard interface.">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme.js"></script>
    <style>
        .action-btns {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn-edit {
            background: #e8edff;
            color: var(--accent);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.8em;
            font-weight: 600;
            transition: opacity 0.15s;
        }
        .btn-delete {
            background: #fff0f0;
            color: var(--danger);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.8em;
            font-weight: 600;
            transition: opacity 0.15s;
        }
        html.dark .btn-edit {
            background: #232b4a;
            color: #b9c6ff;
        }
        html.dark .btn-delete {
            background: #3a1f1f;
            color: #ff9b9b;
        }
        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.8;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .alert-info {
            background: #e8edff;
            color: var(--accent);
            border: 1px solid #c3d0f7;
        }
        html.dark .alert-info {
            background: #232b4a;
            color: #b9c6ff;
            border-color: #34406b;
        }
    </style>
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
            <a href="view_task.php" class="nav-link active">
                <span class="nav-icon">📋</span> My Tasks
            </a>
            <a href="create_task.php" class="nav-link">
                <span class="nav-icon">➕</span> Create Task
            </a>
            <a href="search_task.php" class="nav-link">
                <span class="nav-icon">🔍</span> Search Tasks
            </a>
            <a href="filter_task.php" class="nav-link">
                <span class="nav-icon">⚙️</span> Filter Tasks
            </a>
        </nav>
        <a href="logout.php" class="sidebar-logout">🚪 Logout</a>
    </aside>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Topbar -->
        <header class="topbar">
            <div>
                <h1 class="page-title">My Tasks</h1>
                <p class="page-date"><?= $currentDate ?></p>
            </div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
                <div class="user-badge">👋 Hello, <strong><?= htmlspecialchars($username) ?></strong></div>
            </div>
        </header>

        <!-- Stats Card Grid -->
        <div class="stat-grid" style="margin-bottom: 24px;">
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">📋</span><span class="stat-label">Total Tasks</span></div>
                <div class="stat-number"><?= $counts['total'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">⏳</span><span class="stat-label">Pending</span></div>
                <div class="stat-number"><?= $counts['pending'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">🔄</span><span class="stat-label">In Progress</span></div>
                <div class="stat-number"><?= $counts['inprogress'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">✅</span><span class="stat-label">Completed</span></div>
                <div class="stat-number"><?= $counts['completed'] ?? 0 ?></div>
            </div>
        </div>

        <!-- Filter Bar Panel -->
        <form method="GET" action="view_task.php" class="filter-panel">
            <div class="filter-grid">
                <div class="filter-field">
                    <label class="filter-label">Search</label>
                    <input type="text" name="search" class="filter-select" placeholder="Search title/desc..." value="<?= htmlspecialchars($search_query) ?>">
                </div>
                <div class="filter-field">
                    <label class="filter-label">Status</label>
                    <select name="status" class="filter-select">
                        <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">Priority</label>
                    <select name="priority" class="filter-select">
                        <option value="all" <?= $filter_priority === 'all' ? 'selected' : '' ?>>All Priorities</option>
                        <option value="High" <?= $filter_priority === 'High' ? 'selected' : '' ?>>🔴 High</option>
                        <option value="Medium" <?= $filter_priority === 'Medium' ? 'selected' : '' ?>>🟡 Medium</option>
                        <option value="Low" <?= $filter_priority === 'Low' ? 'selected' : '' ?>>🟢 Low</option>
                    </select>
                </div>
                <div class="filter-field">
                    <label class="filter-label">Category</label>
                    <select name="category" class="filter-select">
                        <option value="all" <?= $filter_category === 'all' ? 'selected' : '' ?>>All Categories</option>
                        <?php
                        $cats = ['Homework', 'Project', 'Exam', 'Assignment', 'Personal', 'Other'];
                        foreach ($cats as $c) {
                            $sel = ($filter_category === $c) ? 'selected' : '';
                            echo "<option value=\"$c\" $sel>$c</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary">Apply Filters</button>
                <a href="view_task.php" class="btn-secondary">Reset</a>
                <a href="create_task.php" class="btn-primary" style="margin-left: auto; background: var(--success);">➕ Create Task</a>
            </div>
        </form>

        <?php if ($delete_msg): ?>
            <div class="alert alert-info" id="delete-alert">🗑️ <?= htmlspecialchars($delete_msg) ?></div>
        <?php endif; ?>

        <!-- Table Wrap -->
        <div class="table-wrap">
            <table class="task-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Task</th>
                        <th>Category</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="7" class="empty-cell">📭 No tasks found. Try adjusting your filters or creating a new task.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $i => $t): 
                            $days_over = intval($t['days_overdue']);
                            $row_class = ($days_over > 0 && $t['status'] !== 'Completed') ? (($days_over > 5) ? 'row-critical' : 'row-overdue') : '';
                        ?>
                        <tr class="<?= $row_class ?>">
                            <td><?= $i + 1 ?></td>
                            <td class="task-title">
                                <?= htmlspecialchars($t['title']) ?>
                                <?php if ($days_over > 5 && $t['status'] !== 'Completed'): ?>
                                    <span class="overdue-tag">🔴 <?= $days_over ?>d overdue</span>
                                <?php elseif ($days_over > 0 && $t['status'] !== 'Completed'): ?>
                                    <span class="overdue-tag-mild">⚠️ <?= $days_over ?>d overdue</span>
                                <?php endif; ?>
                                <?php if (!empty($t['description'])): ?>
                                    <div style="font-size: 0.8em; color: var(--muted); font-weight: normal; margin-top: 3px;">
                                        <?= htmlspecialchars(mb_substr($t['description'], 0, 70)) . (mb_strlen($t['description']) > 70 ? '…' : '') ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="cat-tag"><?= htmlspecialchars($t['category']) ?></span></td>
                            <td class="date-cell <?= ($days_over > 5 && $t['status'] !== 'Completed') ? 'date-critical' : (($days_over > 0 && $t['status'] !== 'Completed') ? 'date-overdue' : '') ?>">
                                <?= !empty($t['due_date']) ? date('d M Y', strtotime($t['due_date'])) : '—' ?>
                            </td>
                            <td><?= priorityBadge($t['priority']) ?></td>
                            <td><?= statusBadge($t['status']) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="update_task.php?id=<?= $t['task_id'] ?>" class="btn-edit" title="Edit task">✏️ Edit</a>
                                    <a href="view_task.php?delete=<?= $t['task_id'] ?>" class="btn-delete" title="Delete task" onclick="return confirm('Are you sure you want to delete this task?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    // Auto-dismiss delete alert after 3 seconds
    const delAlert = document.getElementById('delete-alert');
    if (delAlert) {
        setTimeout(() => {
            delAlert.style.transition = 'opacity 0.5s';
            delAlert.style.opacity   = '0';
            setTimeout(() => delAlert.remove(), 500);
        }, 3000);
    }
</script>
</body>
</html>
