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

    // Delete conversations associated with the current group and the deleted user
    $sql_delete_conversations = "DELETE cu FROM conversation_user AS cu
        INNER JOIN conversation AS c ON cu.id_conversation = c.id
        WHERE c.id_group = ? AND cu.id_user = ? AND c.is_group_conversation = 1";

    $stmt_conversations = mysqli_prepare($conn, $sql_delete_conversations);
    if (!$stmt_conversations) {
        die('Error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_conversations, "ii", $group_id, $user_id);
    mysqli_stmt_execute($stmt_conversations);
    mysqli_stmt_close($stmt_conversations);

    // Delete the user from the group_user table
    $sql_delete_group_user = "DELETE FROM group_user WHERE id_group = ? AND id_user = ?";

    $stmt_group_user = mysqli_prepare($conn, $sql_delete_group_user);
    if (!$stmt_group_user) {
        die('Error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_group_user, "ii", $group_id, $user_id);
    mysqli_stmt_execute($stmt_group_user);
    mysqli_stmt_close($stmt_group_user);

    // Redirect back to the edit group page
