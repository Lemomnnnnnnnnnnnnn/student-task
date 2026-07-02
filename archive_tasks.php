<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

// Get archived tasks
$sql = "SELECT * FROM tasks WHERE archived=1 ORDER BY due_date DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <title>Archive Tasks</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            margin: 30px;
        }

        .container {
            width: 95%;
            margin: auto;
        }

        .back {
            display: inline-block;
            background: #2d4cff;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        h1 {
            color: #2d4cff;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2d4cff;
            color: white;
            padding: 12px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        .restore-btn {

            background: #28a745;
            color: white;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;

        }

        .restore-btn:hover {

            background: #1f8a37;

        }
    </style>

</head>

<body>

    <div class="container">

        <a href="dashboard.php" class="back">← Dashboard</a>

        <h1>Archive Tasks</h1>

        <div class="section">

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

                if (mysqli_num_rows($result) > 0) {

                    while ($row = mysqli_fetch_assoc($result)) {

                        ?>

                        <tr>

                            <td><?php echo $row['id']; ?></td>

                            <td><?php echo htmlspecialchars($row['title']); ?></td>

                            <td><?php echo htmlspecialchars($row['category']); ?></td>

                            <td><?php echo $row['priority']; ?></td>

                            <td><?php echo $row['due_date']; ?></td>

                            <td>📦 Archived</td>

                            <td>

                                <a class="restore-btn" href="restore_task.php?id=<?php echo $row['id']; ?>">
                                    Restore
                                </a>

                            </td>

                        </tr>

                        <?php

                    }

                } else {

                    echo "<tr><td colspan='7'>No archived tasks.</td></tr>";

                }

                ?>

            </table>

        </div>

    </div>

</body>

</html>