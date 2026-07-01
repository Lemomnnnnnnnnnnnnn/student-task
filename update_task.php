<?php
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error   = "";
$success = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_task.php");
    exit();
}

$task_id = (int) $_GET['id'];

// Fetch task
$stmt = $conn->prepare("SELECT * FROM tasks WHERE task_id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$task   = $result->fetch_assoc();
$stmt->close();

if (!$task) {
    header("Location: view_task.php?error=notfound");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date    = trim($_POST['due_date']);
    $category    = $_POST['category'];
    $priority    = $_POST['priority'];
    $status      = $_POST['status'];

    if ($due_date === "") {
        $due_date = null;
    }

    if (empty($title)) {
        $error = "Task title is required.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE tasks
             SET title = ?, description = ?, due_date = ?, category = ?, priority = ?, status = ?
             WHERE task_id = ? AND user_id = ?"
        );
        $stmt->bind_param(
            "ssssssii",
            $title, $description, $due_date, $category, $priority, $status,
            $task_id, $user_id
        );

        if ($stmt->execute()) {
            $success = "Task updated successfully!";
            // Update the local array to reflect new changes in the form
            $task['title']       = $title;
            $task['description'] = $description;
            $task['due_date']    = $due_date;
            $task['category']    = $category;
            $task['priority']    = $priority;
            $task['status']      = $status;
        } else {
            $error = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}

$username    = $_SESSION['username'] ?? 'User';
$currentDate = date("l, d F Y");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Task | Student To-Do List</title>
    <meta name="description" content="Update details of your existing task.">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme.js"></script>
    <style>
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 18px;
        }
        .form-group label {
            font-size: 0.75em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
        }
        .form-control {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.9em;
            background: var(--surface);
            color: var(--text);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            width: 100%;
            font-family: inherit;
        }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(94, 129, 244, 0.15);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 18px;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fff0f0;
            color: var(--danger);
            border: 1px solid #ffe0e0;
        }
        .alert-success {
            background: #eefdf4;
            color: var(--success);
            border: 1px solid #dcfbe7;
        }
        html.dark .alert-error {
            background: #3a1f1f;
            color: #ff9b9b;
            border-color: #5a2f2f;
        }
        html.dark .alert-success {
            background: #1e3527;
            color: #8be8aa;
            border-color: #274d35;
        }
        @media (max-width: 500px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
            <a href="view_task.php" class="nav-link">
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
                <h1 class="page-title">Update Task</h1>
                <p class="page-date"><?= $currentDate ?></p>
            </div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
                <div class="user-badge">👋 Hello, <strong><?= htmlspecialchars($username) ?></strong></div>
            </div>
        </header>

        <!-- Form Card -->
        <div class="dash-panel" style="max-width: 650px; margin: 0 auto;">
            <div class="panel-header">
                <div class="panel-left">
                    <span class="panel-icon fire" style="background: #e8edff; color: var(--accent);">✏️</span>
                    <div>
                        <h3>Edit Task</h3>
                        <p>Modify task details and save changes.</p>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding: 24px;">

                <?php if ($error): ?>
                    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" id="alert-success">✅ <?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="update_task.php?id=<?= $task_id ?>" id="updateTaskForm">

                    <!-- Title -->
                    <div class="form-group">
                        <label for="title">📌 Task Title <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" required
                               value="<?= htmlspecialchars($task['title']) ?>">
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">📝 Description</label>
                        <textarea id="description" name="description" class="form-control"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Date & Category -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="due_date">📅 Due Date</label>
                            <input type="date" id="due_date" name="due_date" class="form-control"
                                   value="<?= htmlspecialchars($task['due_date'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="category">🗂️ Category</label>
                            <select id="category" name="category" class="form-control">
                                <?php
                                $categories = ['Homework', 'Project', 'Exam', 'Assignment', 'Personal', 'Other'];
                                foreach ($categories as $cat) {
                                    $sel = ($task['category'] === $cat) ? 'selected' : '';
                                    echo "<option value=\"$cat\" $sel>$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Priority & Status -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="priority">🔥 Priority</label>
                            <select id="priority" name="priority" class="form-control">
                                <?php
                                $priorities = ['Low', 'Medium', 'High'];
                                foreach ($priorities as $p) {
                                    $sel = ($task['priority'] === $p) ? 'selected' : '';
                                    echo "<option value=\"$p\" $sel>$p</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">📊 Status</label>
                            <select id="status" name="status" class="form-control">
                                <?php
                                $statuses = ['Pending', 'In Progress', 'Completed'];
                                foreach ($statuses as $s) {
                                    $sel = ($task['status'] === $s) ? 'selected' : '';
                                    echo "<option value=\"$s\" $sel>$s</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary" style="flex: 1; justify-content: center;">💾 Save Changes</button>
                        <a href="view_task.php" class="btn-secondary">Cancel</a>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<script>
    // Auto-dismiss alert after 4 seconds
    const successAlert = document.getElementById('alert-success');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s';
            successAlert.style.opacity   = '0';
            setTimeout(() => successAlert.remove(), 500);
        }, 4000);
    }
</script>
</body>
</html>
