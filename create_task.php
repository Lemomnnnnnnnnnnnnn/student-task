<?php
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date    = trim($_POST['due_date']);
    $category    = $_POST['category'];
    $priority    = $_POST['priority'];

    // Convert empty due date string to null
    if ($due_date === "") {
        $due_date = null;
    }

    // Basic validation
    if (empty($title)) {
        $error = "Task title is required.";
    } elseif ($due_date !== null && strtotime($due_date) < strtotime(date('Y-m-d'))) {
        $error = "Due date cannot be in the past.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO tasks (user_id, title, description, due_date, category, priority)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssss", $user_id, $title, $description, $due_date, $category, $priority);

        if ($stmt->execute()) {
            $success = "Task created successfully!";
            // Clear fields on success
            $_POST = array();
        } else {
            $error = "Something went wrong. Please try again. " . $stmt->error;
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
    <title>Create Task | Student To-Do List</title>
    <meta name="description" content="Create a new task with title, description, due date, category and priority.">
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
            <a href="create_task.php" class="nav-link active">
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
                <h1 class="page-title">Create Task</h1>
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
                    <span class="panel-icon fire">➕</span>
                    <div>
                        <h3>New Task Details</h3>
                        <p>Fill in the details below to add a new task to your scheduler.</p>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding: 24px;">

                <?php if ($error): ?>
                    <div class="alert alert-error" id="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" id="alert-success">✅ <?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="create_task.php" id="createTaskForm">

                    <!-- Title -->
                    <div class="form-group">
                        <label for="title">📌 Task Title <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Prepare for Chemistry Midterm" required
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">📝 Description</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Describe the task details, links, or notes..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <!-- Date & Category -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="due_date">📅 Due Date</label>
                            <input type="date" id="due_date" name="due_date" class="form-control"
                                   min="<?= date('Y-m-d') ?>"
                                   value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="category">🗂️ Category</label>
                            <select id="category" name="category" class="form-control">
                                <?php
                                $categories = ['Homework', 'Project', 'Exam', 'Assignment', 'Personal', 'Other'];
                                $selectedCategory = $_POST['category'] ?? 'Other';
                                foreach ($categories as $cat) {
                                    $sel = ($selectedCategory === $cat) ? 'selected' : '';
                                    echo "<option value=\"$cat\" $sel>$cat</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Priority -->
                    <div class="form-group">
                        <label for="priority">🔥 Priority</label>
                        <select id="priority" name="priority" class="form-control">
                            <?php
                            $priorities = ['Low', 'Medium', 'High'];
                            $selectedPriority = $_POST['priority'] ?? 'Medium';
                            foreach ($priorities as $p) {
                                $sel = ($selectedPriority === $p) ? 'selected' : '';
                                echo "<option value=\"$p\" $sel>$p</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary" style="flex: 1; justify-content: center;">🚀 Create Task</button>
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
