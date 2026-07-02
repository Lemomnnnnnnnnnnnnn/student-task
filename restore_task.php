<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";


if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $sql = "UPDATE tasks
            SET archived = 0,
                status = 'Pending'
            WHERE id = $id";

    if (mysqli_query($conn, $sql)) {


        header("Location: status_management.php");
        exit();

    } else {

        die("MySQL Error: " . mysqli_error($conn));

    }

} else {

    die("No ID received.");

}
?>