<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

if (isset($_POST['id']) && isset($_POST['status'])) {

    $id = intval($_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Update task status only
    $sql = "UPDATE tasks SET status='$status' WHERE id=$id";

    if (mysqli_query($conn, $sql)) {

        // Keep task active (do NOT archive automatically)
        mysqli_query($conn, "UPDATE tasks SET archived=0 WHERE id=$id");

        // Return to Status Management
        header("Location: status_management.php");
        exit();

    } else {

        die("MySQL Error: " . mysqli_error($conn));

    }

} else {

    die("Invalid Request");

}
?>