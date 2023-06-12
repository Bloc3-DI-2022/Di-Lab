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
    // Retrieve the group ID and user ID from the form
    $group_id = $_POST['group_id'];
    $user_id = $_POST['user_id'];

    // Delete the user from the group_user table
    $sql_delete = "DELETE FROM group_user WHERE id_group = ? AND id_user = ?";

    $stmt = mysqli_prepare($conn, $sql_delete);
    if (!$stmt) {
        die('Error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ii", $group_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Redirect back to the edit group page
    header("location: edit_group.php?id=$group_id");
    exit;
}

mysqli_close($conn);
?>
