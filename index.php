<?php
session_start();

// Check if session is logged in and redirect
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Redirect to chat.php with the conversation ID
    $conversation_id = $_SESSION["conversation_id"];
    header("location: chat.php?conversation_id=$conversation_id");
    exit;
}

$email = $password = "";
$email_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Form email is empty?
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Form password is empty?
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check password and email in the database and validate
    if (empty($email_err) && empty($password_err)) {

        // Connect to the database
        $conn = mysqli_connect("localhost", "root", "", "chat_db_2");

        // Check the database connection
        if ($conn === false) {
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }

        // Prepare a select statement
        $sql_email = "SELECT id, email, password FROM user WHERE email = ?";

        if ($stmt = mysqli_prepare($conn, $sql_email)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);

            // Set parameters
            $param_email = $email;

            // Execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                // Check if the email exists
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Start the session if the password is correct
                            session_start();

                            // Session data
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;

                            // Get the conversation ID for the user and bot "DI Lab"
                            $sql_conversation = "SELECT id_conversation FROM conversation_user WHERE id_user = 3";
                            if ($stmt_conversation = mysqli_prepare($conn, $sql_conversation)) {
                                mysqli_stmt_execute($stmt_conversation);
                                mysqli_stmt_bind_result($stmt_conversation, $conversation_id);
                                if (mysqli_stmt_fetch($stmt_conversation)) {
                                    $_SESSION["conversation_id"] = $conversation_id;

                                    // Redirect to chat.php with the conversation ID
                                    header("location: chat.php?conversation_id=$conversation_id");
                                }
                                mysqli_stmt_close($stmt_conversation);
                            } else {
                                echo "Error: " . $conn->error;
                            }
                        } else {
                            $password_err = "Wrong password.";
                            // Debugging
                            // echo "Associated password: " . $hashed_password;
                        }
                    }
                } else {
                    $email_err ="Email not found.";
                }
            } else {
                echo "Error undefined";
            }
            mysqli_stmt_close($stmt);
        }

        // Close the database connection
        mysqli_close($conn);
    }
}

?><DOCTYPE html>

<head>
<title>Connexion DiLAB</title>
<meta charset="utf-8">
<link rel="stylesheet" media="screen" href="connexion.css"/>
</head>

<body>
<h2>Login</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Email:</label>
        <input type="text" name="email" value="<?php echo $email; ?>">
        <span><?php echo $email_err; ?></span>
    </div>
    <div>
        <label>Password:</label>
        <input type="password" name="password">
        <?php if (!empty($password_err)) : ?>
            <span><?php echo $password_err; ?></span>
        <?php endif; ?>
    </div>
    <div>
        <input type="submit" value="Login">
    </div>
</form>
<p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
</body>

</DOCTYPE>
    