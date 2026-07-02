<?php
include "includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// ── Handle Profile Updates ──────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email)) {
        $error = "Username and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email already exists for another user
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $user_id);
        $check->execute();
        $check_res = $check->get_result();
        
        if ($check_res->num_rows > 0) {
            $error = "This email is already registered to another account.";
        } else {
            $check->close();
            
            // Start updating
            if (!empty($password)) {
                if ($password !== $confirm_password) {
                    $error = "New passwords do not match.";
                } elseif (strlen($password) < 6) {
                    $error = "Password must be at least 6 characters long.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
                    if ($stmt->execute()) {
                        $success = "Profile and password updated successfully!";
                        $_SESSION['username'] = $username;
                    } else {
                        $error = "Failed to update profile. " . $conn->error;
                    }
                    $stmt->close();
                }
            } else {
                // Update without password
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $email, $user_id);
                if ($stmt->execute()) {
                    $success = "Profile updated successfully!";
                    $_SESSION['username'] = $username;
                } else {
                    $error = "Failed to update profile. " . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

// ── Fetch current user details ──────────────────────────────────────
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();
$user_query->close();

// ── Fetch task statistics ───────────────────────────────────────────
$stats_query = $conn->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(status = 'Pending') AS pending,
        SUM(status = 'In Progress') AS in_progress,
        SUM(status = 'Completed') AS completed,
        SUM(due_date < CURDATE() AND status != 'Completed') AS overdue
    FROM tasks 
    WHERE user_id = ?
");
$stats_query->bind_param("i", $user_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();
$stats_query->close();

$total_tasks   = $stats['total'] ?? 0;
$pending_tasks = $stats['pending'] ?? 0;
$progress_tasks= $stats['in_progress'] ?? 0;
$done_tasks    = $stats['completed'] ?? 0;
$overdue_tasks = $stats['overdue'] ?? 0;
$pct           = $total_tasks > 0 ? round($done_tasks / $total_tasks * 100) : 0;

// Page parameters
$page_title = "My Profile";
$page_active = "profile";
include "includes/header.php";
?>

<div class="profile-grid">
    <!-- Left Panel: Avatar & Stats -->
    <div class="profile-card">
        <div class="avatar-container">
            <div class="profile-avatar">
                <?= htmlspecialchars(mb_substr($user['username'], 0, 1)) ?>
            </div>
        </div>
        <h2 class="profile-name"><?= htmlspecialchars($user['username']) ?></h2>
        <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
        
        <div class="profile-meta-list">
            <div class="profile-meta-item">
                <span>Account Status:</span>
                <strong>Active</strong>
            </div>
            <?php if (!empty($user['created_at'])): ?>
                <div class="profile-meta-item">
                    <span>Member Since:</span>
                    <strong><?= date("d M Y", strtotime($user['created_at'])) ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <div class="progress-card" style="padding: 12px; margin-top: 20px; margin-bottom: 0; box-shadow: none; border-color: var(--border);">
            <div class="progress-meta" style="min-width: unset; flex: 1;">
                <span style="font-size: 0.85em; font-weight: 600;">Completion Rate</span>
            </div>
            <div class="progress-track" style="height: 6px;">
                <div class="progress-fill" style="width:<?= $pct ?>%"></div>
            </div>
            <div class="progress-pct" style="font-size: 0.9em;"><?= $pct ?>%</div>
        </div>

        <div class="stats-compact">
            <div class="stat-box">
                <div class="stat-box-num"><?= $total_tasks ?></div>
                <div class="stat-box-lbl">Total Tasks</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-num" style="color: var(--success);"><?= $done_tasks ?></div>
                <div class="stat-box-lbl">Completed</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-num" style="color: var(--accent);"><?= $progress_tasks + $pending_tasks ?></div>
                <div class="stat-box-lbl">Active</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-num" style="color: var(--danger);"><?= $overdue_tasks ?></div>
                <div class="stat-box-lbl">Overdue</div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Edit Details Form -->
    <div class="dash-panel">
        <div class="panel-header">
            <div class="panel-left">
                <span class="panel-icon fire">⚙️</span>
                <div>
                    <h3>Edit Profile Details</h3>
                    <p>Change your account details and update your security credentials.</p>
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

            <form method="POST" action="profile.php" id="profileForm">
                <input type="hidden" name="update_profile" value="1">
                
                <!-- Username -->
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">📧 Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div style="border-top: 1px solid var(--border); margin: 24px 0 16px; padding-top: 16px;">
                    <h4 style="font-size: 0.9em; font-weight: 700; text-transform: uppercase; color: var(--muted); margin-bottom: 4px;">Update Password</h4>
                    <p style="font-size: 0.8em; color: var(--muted);">Leave blank if you do not want to change your password.</p>
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label for="password">🔒 New Password</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter at least 6 characters">
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">🔒 Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Retype your new password">
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn-primary" style="justify-content: center; width: 100%; padding: 12px;">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Auto-dismiss alerts after 4 seconds
    const successAlert = document.getElementById('alert-success');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s';
            successAlert.style.opacity   = '0';
            setTimeout(() => successAlert.remove(), 500);
        }, 4000);
    }
</script>

<?php
include "includes/footer.php";
?>
