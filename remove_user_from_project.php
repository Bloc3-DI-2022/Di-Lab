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
    // Retrieve the project ID and user ID from the form
    $project_id = $_POST['project_id'];
    $user_id = $_POST['user_id'];

    // Delete the user from the project_user table
    $sql_delete = "DELETE FROM project_user WHERE project_id = ? AND user_id = ?";

    $stmt = mysqli_prepare($conn, $sql_delete);
    if (!$stmt) {
        die('Error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ii", $project_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Redirect back to the edit project page
    header("location: edit_project.php?id=$project_id");
    exit;
}

mysqli_close($conn);
?>
