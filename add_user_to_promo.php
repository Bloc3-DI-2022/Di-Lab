<?php
session_start();

require_once 'db_connection.php';

// Redirect to login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: index.php");
  exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve the promo ID and user ID from the form
  $promo_id = $_POST['promo_id'];
  $user_id = $_POST['user_id'];

  // Insert the user into the promo_user table
  $sql_insert = "INSERT INTO promo_user (id_promo, id_user) VALUES ('$promo_id', '$user_id')";

  if (mysqli_query($conn, $sql_insert)) {
    // Redirect back to the edit promo page
    header("location: edit_promo.php?id=$promo_id");
    exit;
  } else {
    // Handle the case when the insert fails
    // For example, display an error message or redirect to an error page
    echo "Error adding user to promo: " . mysqli_error($conn);
  }
}

mysqli_close($conn);
?>
