<?php
require 'db_connection.php';

// Get form data
$user_id = mysqli_real_escape_string($conn, htmlspecialchars($_POST['id']));
$first_name = mysqli_real_escape_string($conn, htmlspecialchars($_POST['first_name']));
$last_name = mysqli_real_escape_string($conn, htmlspecialchars($_POST['last_name']));
$email = mysqli_real_escape_string($conn, htmlspecialchars($_POST['email']));
$password = mysqli_real_escape_string($conn, htmlspecialchars($_POST['password']));
$user_type_id = mysqli_real_escape_string($conn, htmlspecialchars($_POST['user_type_id']));
$promo_id = mysqli_real_escape_string($conn, htmlspecialchars($_POST['promo_id']));

// Update user details
$sql = "UPDATE user SET first_name = ?, last_name = ?, email = ?, password = ?, user_type_id = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", $first_name, $last_name, $email, $password, $user_type_id, $user_id);
$stmt->execute();

// Check if a new promo was selected
if ($promo_id != "") {
    // Check if user is already associated with the selected promo
    $sql = "SELECT * FROM promo_user WHERE id_user = ? AND id_promo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $promo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If user is not already associated with the selected promo, add the association
    if ($result->num_rows == 0) {
        $sql = "INSERT INTO promo_user (id_user, id_promo) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $promo_id);
        $stmt->execute();
    }
}

// Redirect back to the edit user page
header("Location: edit_user.php?id=" . $user_id);
?>
