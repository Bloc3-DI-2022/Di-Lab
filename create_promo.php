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
  // Retrieve the promo details from the form
  $name = $_POST['name'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $pilot_user_id = $_POST['pilot'];

  // Insert the promo into the database
  $sql_insert = "INSERT INTO promo (name, start_date, end_date, pilote_user_id) VALUES ('$name', '$start_date', '$end_date', '$pilot_user_id')";

  if (mysqli_query($conn, $sql_insert)) {
    // Get the ID of the newly created promo
    $promo_id = mysqli_insert_id($conn);
    
    // Redirect to the edit promo page of the newly created promo
    header("location: edit_promo.php?id=$promo_id");
    exit;
  } else {
    // Handle the case when the insert fails
    // For example, display an error message or redirect to an error page
    echo "Error creating promo: " . mysqli_error($conn);
  }
}

mysqli_close($conn);
?>
