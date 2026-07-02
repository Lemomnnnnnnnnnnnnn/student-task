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
<?php
$page_title = "Update Task";
$page_active = "view_tasks";
include "includes/header.php";
?>

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

<?php
$extra_scripts = '
<script>
    // Auto-dismiss alert after 4 seconds
    const successAlert = document.getElementById(\'alert-success\');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = \'opacity 0.5s\';
            successAlert.style.opacity   = \'0\';
            setTimeout(() => successAlert.remove(), 500);
        }, 4000);
    }
</script>
';
include "includes/footer.php";
?>
