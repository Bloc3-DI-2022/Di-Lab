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

    // Insert the user into the group_user table
    $sql_insert = "INSERT INTO group_user (id_group, id_user) VALUES ('$group_id', '$user_id')";

    if (mysqli_query($conn, $sql_insert)) {
        // Redirect back to the edit group page
        header("location: edit_group.php?id=$group_id");
        exit;
    } else {
        // Handle the case when the insert fails
        // For example, display an error message or redirect to an error page
        echo "Error adding user to group: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
