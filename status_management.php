<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

// Active Tasks
$active = mysqli_query($conn, "SELECT * FROM tasks WHERE archived=0 ORDER BY due_date ASC");

// Archived Tasks
$archived = mysqli_query($conn, "SELECT * FROM tasks WHERE archived=1 ORDER BY due_date DESC");
?>

<!DOCTYPE html>
<html lang="en" class="status-mgmt-page">

<head>
    <meta charset="UTF-8">
    <title>Status Management</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <div class="container">

        <a href="dashboard.php" class="back">← Dashboard</a>

        <h1>Status Management</h1>

        <!-- ACTIVE TASKS -->

        <div class="section">

            <h2>📋 Active Tasks</h2>

            <table>

                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php

                if (mysqli_num_rows($active) > 0) {

                    while ($row = mysqli_fetch_assoc($active)) {
                        ?>

                        <tr>

                            <td><?php echo $row['id']; ?></td>

                            <td><?php echo htmlspecialchars($row['title']); ?></td>

                            <td><?php echo htmlspecialchars($row['category']); ?></td>

                            <td><?php echo $row['priority']; ?></td>

                            <td><?php echo $row['due_date']; ?></td>

                            <td>

                                <form action="update_status.php" method="POST">

                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                    <select name="status">

                                        <option value="Pending" <?php if ($row['status'] == "Pending")
                                            echo "selected"; ?>>
                                            Pending
                                        </option>

                                        <option value="On-going" <?php if ($row['status'] == "On-going")
                                            echo "selected"; ?>>
                                            On-going
                                        </option>

                                        <option value="Completed" <?php if ($row['status'] == "Completed")
                                            echo "selected"; ?>>
                                            Completed
                                        </option>

                                    </select>

                            </td>

                            <td>

                                <button type="submit">
                                    Update
                                </button>

                                </form>

                            </td>

                        </tr>

                        <?php
                    }

                } else {

                    echo "<tr><td colspan='7'>No active tasks.</td></tr>";

                }
                ?>

            </table>

        </div>

        <!-- ARCHIVED TASKS -->

        <div class="section">

            <h2>📦 Completed / Archived Tasks</h2>

            <table>

                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php

                if (mysqli_num_rows($archived) > 0) {

                    while ($row = mysqli_fetch_assoc($archived)) {
                        ?>

                        <tr class="completed">

                            <td><?php echo $row['id']; ?></td>

                            <td><?php echo htmlspecialchars($row['title']); ?></td>

                            <td><?php echo htmlspecialchars($row['category']); ?></td>

                            <td><?php echo $row['priority']; ?></td>

                            <td><?php echo $row['due_date']; ?></td>

                            <td>✅ Completed</td>

                            <td>
                                <a class="restore-btn" href="restore_task.php?id=<?php echo $row['id']; ?>"
                                    onclick="return confirm('Restore this task?')">
                                    Restore
                                </a>
                            </td>

                        </tr>

                        <?php
                    }

                } else {

                    echo "<tr><td colspan='7'>No completed tasks.</td></tr>";

                }
                ?>

            </table>

        </div>

    </div>

</body>

</html>