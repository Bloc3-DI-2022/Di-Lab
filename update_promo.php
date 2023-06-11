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
  // Retrieve the promo ID and modified details from the form
  $promo_id = $_POST['promo_id'];
  $name = $_POST['name'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];
  $pilot_user_id = $_POST['pilot'];

  // Update the promo details in the database
  $sql_update = "UPDATE promo SET name = '$name', start_date = '$start_date', end_date = '$end_date', pilote_user_id = '$pilot_user_id' WHERE id = $promo_id";

  if (mysqli_query($conn, $sql_update)) {
    // Redirect the user to the promo list with a success message
    $_SESSION['success_message'] = "Promo updated successfully.";
    header("location: promo.php");
    exit;
  } else {
    // Handle the case when the update fails
    // For example, display an error message or redirect to an error page
    echo "Error updating promo: " . mysqli_error($conn);
  }
}

mysqli_close($conn);
?>
