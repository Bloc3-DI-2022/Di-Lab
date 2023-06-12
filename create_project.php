<?php
session_start();
require_once 'db_connection.php';

// Redirect to login if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the group ID and project name from the form
    $group_id = $_POST['group_id'];
    $project_name = $_POST['project_name'];

    // Insert the project into the database
    $sql_insert = "INSERT INTO project (name, group_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt, "si", $project_name, $group_id);
    if (mysqli_stmt_execute($stmt)) {
        // Redirect back to the edit group page
        header("location: edit_group.php?id=$group_id");
        exit;
    } else {
        // Handle the case when the insert fails
        // For example, display an error message or redirect to an error page
        echo "Error creating project: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
