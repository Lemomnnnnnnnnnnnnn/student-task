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
<?php
$page_title = "Create Task";
$page_active = "create_task";
include "includes/header.php";
?>

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
