<?php
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['username'] ?? 'User';

// Collect filter inputs
$f_category = trim($_GET['category'] ?? '');
$f_priority = trim($_GET['priority'] ?? '');
$f_from     = trim($_GET['from'] ?? '');
$f_to       = trim($_GET['to'] ?? '');

$where_parts = ["user_id = $user_id"];

if ($f_category !== '')
    $where_parts[] = "category = '" . $conn->real_escape_string($f_category) . "'";
if ($f_priority !== '')
    $where_parts[] = "priority = '" . $conn->real_escape_string($f_priority) . "'";
if ($f_from !== '')
    $where_parts[] = "due_date >= '" . $conn->real_escape_string($f_from) . "'";
if ($f_to !== '')
    $where_parts[] = "due_date <= '" . $conn->real_escape_string($f_to) . "'";

$where_sql  = "WHERE " . implode(" AND ", $where_parts);
$is_filtered = count($where_parts) > 1;

$results = $conn->query("
    SELECT *, DATEDIFF(CURDATE(), due_date) AS days_overdue
    FROM tasks
    $where_sql
    ORDER BY due_date ASC
");

// Fetch distinct categories for this user
$cats = $conn->query("SELECT DISTINCT category FROM tasks WHERE user_id = $user_id ORDER BY category ASC");

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
?>
<?php
$page_title = "Filter Tasks";
$page_active = "filter_tasks";
include "includes/header.php";
?>

        <!-- Filter Panel -->
        <form method="GET" action="filter_task.php" class="filter-panel">
            <div class="filter-grid">

                <div class="filter-field">
                    <label class="filter-label">Category</label>
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php if ($cats): ?>
                            <?php while ($cat = $cats->fetch_assoc()): ?>
                                <?php if (!empty($cat['category'])): ?>
                                    <option value="<?= htmlspecialchars($cat['category']) ?>"
                                        <?= $f_category === $cat['category'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="filter-field">
                    <label class="filter-label">Priority</label>
                    <select name="priority" class="filter-select">
                        <option value="">All Priorities</option>
                        <option value="High"   <?= $f_priority === 'High'   ? 'selected' : '' ?>>🔴 High</option>
                        <option value="Medium" <?= $f_priority === 'Medium' ? 'selected' : '' ?>>🟡 Medium</option>
                        <option value="Low"    <?= $f_priority === 'Low'    ? 'selected' : '' ?>>🟢 Low</option>
                    </select>
                </div>

                <div class="filter-field">
                    <label class="filter-label">Due From</label>
                    <input type="date" name="from" class="filter-select" value="<?= htmlspecialchars($f_from) ?>">
                </div>

                <div class="filter-field">
                    <label class="filter-label">Due To</label>
                    <input type="date" name="to" class="filter-select" value="<?= htmlspecialchars($f_to) ?>">
                </div>

            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary">Apply Filters</button>
                <?php if ($is_filtered): ?>
                    <a href="filter_task.php" class="btn-secondary">Clear Filters</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($is_filtered): ?>
        <div class="filter-chips">
            <?php if ($f_category): ?>
                <span class="chip">Category: <?= htmlspecialchars($f_category) ?></span>
            <?php endif; ?>
            <?php if ($f_priority): ?>
                <span class="chip">Priority: <?= htmlspecialchars($f_priority) ?></span>
            <?php endif; ?>
            <?php if ($f_from): ?>
                <span class="chip">From: <?= htmlspecialchars($f_from) ?></span>
            <?php endif; ?>
            <?php if ($f_to): ?>
                <span class="chip">To: <?= htmlspecialchars($f_to) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($is_filtered): ?>
            <?php $count = $results ? $results->num_rows : 0; ?>
            <p class="result-meta"><?= $count ?> task<?= $count !== 1 ? 's' : '' ?> found</p>
        <?php endif; ?>

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
                <?php if ($results && $results->num_rows > 0):
                    while ($row = $results->fetch_assoc()):
                        $days_over = intval($row['days_overdue']);
                        $row_class = ($days_over > 5 && $row['status'] !== 'Completed') ? 'row-critical' : (($days_over > 0 && $row['status'] !== 'Completed') ? 'row-overdue' : '');
                ?>
                    <tr class="<?= $row_class ?>">
                        <td class="task-title">
                            <?= htmlspecialchars($row['title']) ?>
                            <?php if ($days_over > 5 && $row['status'] !== 'Completed'): ?>
                                <span class="overdue-tag">🔴 <?= $days_over ?>d overdue</span>
                            <?php elseif ($days_over > 0 && $row['status'] !== 'Completed'): ?>
                                <span class="overdue-tag-mild">⚠️ <?= $days_over ?>d overdue</span>
                            <?php endif; ?>
                            <?php if (!empty($row['description'])): ?>
                                <div style="font-size: 0.8em; color: var(--muted); font-weight: normal; margin-top: 3px;">
                                    <?= htmlspecialchars(mb_substr($row['description'], 0, 70)) . (mb_strlen($row['description']) > 70 ? '…' : '') ?>
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
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" class="empty-cell">
                            <?= $is_filtered ? 'No tasks match your filters.' : 'No tasks yet.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

<?php
include "includes/footer.php";
?>
