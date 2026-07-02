<?php
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Stat counts (with user isolation)
$total       = $conn->query("SELECT COUNT(*) AS c FROM tasks WHERE user_id = $user_id")->fetch_assoc()['c'];
$in_progress = $conn->query("SELECT COUNT(*) AS c FROM tasks WHERE user_id = $user_id AND status IN ('Pending','In Progress')")->fetch_assoc()['c'];
$completed   = $conn->query("SELECT COUNT(*) AS c FROM tasks WHERE user_id = $user_id AND status='Completed'")->fetch_assoc()['c'];
$overdue     = $conn->query("SELECT COUNT(*) AS c FROM tasks WHERE user_id = $user_id AND due_date < CURDATE() AND status != 'Completed'")->fetch_assoc()['c'];
$pct         = $total > 0 ? round($completed / $total * 100) : 0;

// Overdue panel — worst-overdue first (with user isolation)
$overdue_tasks = $conn->query("
    SELECT *, DATEDIFF(CURDATE(), due_date) AS days_overdue
    FROM tasks
    WHERE user_id = $user_id AND due_date < CURDATE() AND status != 'Completed'
    ORDER BY days_overdue DESC
    LIMIT 5
");

// Due soon panel — closest due date first (with user isolation)
$due_soon_tasks = $conn->query("
    SELECT *, DATEDIFF(due_date, CURDATE()) AS days_left
    FROM tasks
    WHERE user_id = $user_id AND due_date >= CURDATE() AND status != 'Completed'
    ORDER BY due_date ASC
    LIMIT 5
");

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

$page_title = "Dashboard";
$page_active = "dashboard";
include "includes/header.php";
?>

        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-text">
                <h2>Welcome Back, <?= htmlspecialchars($username) ?>!</h2>
                <p>Manage your tasks, track your progress, and stay productive.</p>
            </div>
            <div class="welcome-actions">
                <a href="view_task.php" class="wbtn wbtn--ghost">📋 View My Tasks</a>
                <a href="create_task.php" class="wbtn wbtn--ghost">➕ Create New Task</a>
            </div>
        </div>

        <!-- Stat Cards Grid -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">📋</span><span class="stat-label">Total Tasks</span></div>
                <div class="stat-number"><?= $total ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">🔄</span><span class="stat-label">In Progress</span></div>
                <div class="stat-number"><?= $in_progress ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-top"><span class="stat-icon">✅</span><span class="stat-label">Completed</span></div>
                <div class="stat-number"><?= $completed ?></div>
            </div>
            <div class="stat-card stat-card--warn">
                <div class="stat-top"><span class="stat-icon">⚠️</span><span class="stat-label">Overdue</span></div>
                <div class="stat-number"><?= $overdue ?></div>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="progress-card">
            <div class="progress-meta">
                <span>Overall Progress</span>
                <span><strong><?= $completed ?></strong> of <strong><?= $total ?></strong> tasks completed</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width:<?= $pct ?>%"></div>
            </div>
            <div class="progress-pct"><?= $pct ?>%</div>
        </div>

        <div class="dash-panels">

            <!-- Overdue Panel -->
            <div class="dash-panel dash-panel--red">
                <div class="panel-header">
                    <div class="panel-left">
                        <span class="panel-icon fire">🔥</span>
                        <div>
                            <h3>Overdue Tasks</h3>
                            <p>Needs immediate attention</p>
                        </div>
                    </div>
                    <a href="view_task.php?status=Pending&priority=High" class="panel-see-all red">See All</a>
                </div>
                <div class="panel-body">
                    <?php if ($overdue_tasks->num_rows > 0): ?>
                    <table class="task-table">
                        <thead><tr><th>Title</th><th>Category</th><th>Priority</th><th>Due Date</th><th>Status</th><th>Overdue</th></tr></thead>
                        <tbody>
                        <?php while ($row = $overdue_tasks->fetch_assoc()): ?>
                            <tr class="row-critical">
                                <td class="task-title"><?= htmlspecialchars($row['title']) ?></td>
                                <td><span class="cat-tag"><?= htmlspecialchars($row['category']) ?></span></td>
                                <td><?= priority_badge($row['priority']) ?></td>
                                <td class="date-cell date-critical"><?= date("d M Y", strtotime($row['due_date'])) ?></td>
                                <td><?= status_badge($row['status']) ?></td>
                                <td class="overdue-tag"><?= intval($row['days_overdue']) ?>d</td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state-inline"><span class="empty-icon">🎉</span><p>No overdue tasks — nice work!</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Due Soon Panel -->
            <div class="dash-panel">
                <div class="panel-header">
                    <div class="panel-left">
                        <span class="panel-icon clock">⏰</span>
                        <div>
                            <h3>Due Soon</h3>
                            <p>Upcoming deadlines</p>
                        </div>
                    </div>
                    <a href="view_task.php" class="panel-see-all">See All</a>
                </div>
                <div class="panel-body">
                    <?php if ($due_soon_tasks->num_rows > 0): ?>
                    <table class="task-table">
                        <thead><tr><th>Title</th><th>Category</th><th>Priority</th><th>Due Date</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php while ($row = $due_soon_tasks->fetch_assoc()): ?>
                            <tr>
                                <td class="task-title"><?= htmlspecialchars($row['title']) ?></td>
                                <td><span class="cat-tag"><?= htmlspecialchars($row['category']) ?></span></td>
                                <td><?= priority_badge($row['priority']) ?></td>
                                <td class="date-cell"><?= date("d M Y", strtotime($row['due_date'])) ?></td>
                                <td><?= status_badge($row['status']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="empty-state-inline"><span class="empty-icon">📭</span><p>Nothing due soon.</p></div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
<?php
include "includes/footer.php";
?>