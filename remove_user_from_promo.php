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
    // Retrieve the promo ID and user ID from the form
    $promo_id = $_POST['promo_id'];
    $user_id = $_POST['user_id'];
    
    // Delete the user from the promo_user table
    $sql_delete_promo_user = "DELETE FROM promo_user WHERE id_promo = ? AND id_user = ?";

    $stmt_promo_user = mysqli_prepare($conn, $sql_delete_promo_user);
    if (!$stmt_promo_user) {
        die('Error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_promo_user, "ii", $promo_id, $user_id);
    mysqli_stmt_execute($stmt_promo_user);
    mysqli_stmt_close($stmt_promo_user);

    // Redirect to the edit page of the promo
    header("location: edit_promo.php?id=".$promo_id);
    exit;
}

mysqli_close($conn);
?>