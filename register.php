<?php
ob_start();
require_once 'db_connection.php';

$email = $password = $confirm_password = $first_name = $last_name = "";
$email_err = $password_err = $confirm_password_err = "";

// Check data from POST form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Check empty email
  if (empty(trim($_POST["email"]))) {
    $email_err = "Email must not be empty";
  } else {
    $sql = "SELECT id FROM user WHERE email = ?";

    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("s", $param_email);
      $param_email = trim($_POST["email"]);

      if ($stmt->execute()) {
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
          $email_err = "This email is already registered.";
        } else {
          $email = trim($_POST["email"]);
        }
      } else {
        echo "Error: Undefined";
      }

      $stmt->close();
    }
  }

  // Check if password is valid
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter a password.";
  } elseif (strlen(trim($_POST["password"])) < 6) {
    $password_err = "Password must have at least 6 characters.";
  } else {
    $password = trim($_POST["password"]);
  }

  // Check if passwords are the same
  if (empty(trim($_POST["confirm_password"]))) {
    $confirm_password_err = "Please confirm the password.";
  } else {
    $confirm_password = trim($_POST["confirm_password"]);
    if (empty($password_err) && ($password != $confirm_password)) {
      $confirm_password_err = "Passwords do not match.";
    }
  }

  // Check empty first name
  if (empty(trim($_POST["first_name"]))) {
    $first_name_err = "First name must not be empty";
  } else {
    $first_name = trim($_POST["first_name"]);
  }

  // Check empty last name
  if (empty(trim($_POST["last_name"]))) {
    $last_name_err = "Last name must not be empty";
  } else {
    $last_name = trim($_POST["last_name"]);
  }

  // Insert user into database if there are no errors
  if (empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($first_name_err) && empty($last_name_err)) {
    $sql = "INSERT INTO user (email, password, first_name, last_name) VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param("ssss", $param_email, $param_password, $param_first_name, $param_last_name);
      $param_email = $email;
      $param_password = password_hash($password, PASSWORD_DEFAULT); // Hash password
      $param_first_name = $first_name;
      $param_last_name = $last_name;

      if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Insert the user and the bot/user "DI Lab" into conversation_user
        $sql_user_conversation = "INSERT INTO conversation_user (id_user, id_conversation) VALUES (?, ?), (?, ?)";
        if ($stmt_user_conversation = $conn->prepare($sql_user_conversation)) {
          $bot_id = 3; // ID of the bot/user "DI Lab"
          $conversation_id = 0; // Initialize conversation ID (it will be auto-generated)

          $stmt_user_conversation->bind_param("iiii", $user_id, $conversation_id, $bot_id, $conversation_id);
          if ($stmt_user_conversation->execute()) {
            $conversation_id = $stmt_user_conversation->insert_id;

            // Insert a "hi" message in the conversation
            $message = "hi";
            $sql_message = "INSERT INTO message (id_conversation, id_user, message) VALUES (?, ?, ?)";
            if ($stmt_message = $conn->prepare($sql_message)) {
              $stmt_message->bind_param("iis", $conversation_id, $bot_id, $message);
              $stmt_message->execute();
            } else {
              echo "Error: " . $conn->error;
            }
          } else {
            echo "Error: " . $conn->error;
          }
          $stmt_user_conversation->close();
        } else {
          echo "Error: " . $conn->error;
        }
      } else {
        echo "Error: " . $conn->error;
      }
      $stmt->close();
    } else {
      echo "Error: " . $conn->error;
    }
  }

  $conn->close();
}

ob_end_flush();
?><!DOCTYPE html>
<html>
<head>
<title>Connexion DiLAB</title>
<meta charset="utf-8">
<link rel="stylesheet" media="screen" href="register.css"/>
</head>
<body>
	<h1>Register</h1>
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
  <label>Email:</label>
  <input type="text" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
  <?php if (!empty($email_err)) echo '<span>' . $email_err . '</span>'; ?>

  <label>Password:</label>
  <input type="password" name="password" required>
  <?php if (!empty($password_err)) echo '<span>' . $password_err . '</span>'; ?>

  <label>Confirm Password:</label>
  <input type="password" name="confirm_password" required>
  <?php if (!empty($confirm_password_err)) echo '<span>' . $confirm_password_err . '</span>'; ?>

  <label>First Name:</label>
  <input type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : ''; ?>" required>
  <?php if (!empty($first_name_err)) echo '<span>' . $first_name_err . '</span>'; ?>

  <label>Last Name:</label>
  <input type="text" name="last_name" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : ''; ?>" required>
  <?php if (!empty($last_name_err)) echo '<span>' . $last_name_err . '</span>'; ?>


  <input type="submit" value="Register">
  <p>Already have an account? <a href="index.php">Login here</a>.</p>
</form>
</body>
</html>
