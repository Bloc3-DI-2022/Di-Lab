<?php
session_start();
require_once 'db_connection.php';

// Redirect to login if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}
$conn = mysqli_connect('localhost', 'root', '', 'chat_db_2');
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Fetch all conversations for the logged-in user
$sql = "SELECT * FROM conversation_user WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die('Error: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$conversations = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch messages for the selected conversation
$messages = [];
if (isset($_GET['conversation_id'])) {
    $messages = getMessages($_GET['conversation_id']);
}



function getConversationName($conversation_id) {
    global $conn;
    $sql = "SELECT name FROM conversation WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $conversation_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return isset($row['name']) ? $row['name'] : "";
    } else {
        // Handle the case when statement preparation fails
        // For example, display an error message or redirect to an error page
        echo "Error preparing statement: " . mysqli_error($conn);
        return "";
    }
}


function getMessages($conversation_id) {
    global $conn;
    $sql = "SELECT m.*, u.first_name, u.last_name FROM message AS m
            INNER JOIN user AS u ON m.id_user = u.id
            WHERE m.id_conversation = ?
            ORDER BY m.date ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $conversation_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $messages;
}

function getLastMessage($conversation_id) {
    global $conn;
    $sql = "SELECT message FROM message WHERE id_conversation = ? ORDER BY date DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $conversation_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return isset($row['message']) ? $row['message'] : "";
}

function getLastSender($conversation_id) {
    global $conn;
    $sql = "SELECT user.first_name, user.last_name FROM message JOIN user ON message.id_user = user.id WHERE message.id_conversation = ? ORDER BY message.date DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $conversation_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return isset($row['first_name']) && isset($row['last_name']) ? $row['first_name'] . " " . $row['last_name'] : "";
}

function getLastDateTime($conversation_id) {
    global $conn;
    $sql = "SELECT date FROM message WHERE id_conversation = ? ORDER BY date DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $conversation_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return isset($row['date']) ? $row['date'] : "";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'];
    $color = isset($_POST['color']) ? $_POST['color'] : ""; // Set default color if not selected
    $new_filename = "";
    $conversation_id = $_GET['conversation_id'];

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $filename = basename($_FILES['image']['name']);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $extension;
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $new_filename);
    }

    $sql = "INSERT INTO message (id_user, id_conversation, message, filepath, color, date) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iisss", $_SESSION["id"], $conversation_id, $message, $new_filename, $color);
    mysqli_stmt_execute($stmt); // Execute the prepared statement
    mysqli_stmt_close($stmt);

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER["PHP_SELF"] . "?conversation_id=" . $conversation_id);
    exit;
}
include("fonction.php");

?>
<html>

<head>
    <title>Chat Room CESI-DI 2022</title>
    <meta charset="utf-8">
    <link rel="stylesheet" media="screen" href="chat.css"/>
    
</head>
<body>
    <div class="nav">
    <?php get_include("header.php"); ?> 
    </div>
<div class="container-chat">
    <div class="sidebar">
        <h2>Conversations</h2>
        <?php foreach ($conversations as $conversation): ?>
            <?php
                $lastMessage = getLastMessage($conversation['id_conversation']);
                $lastSender = getLastSender($conversation['id_conversation']);
                $lastDateTime = getLastDateTime($conversation['id_conversation']);
            ?>
            <div class="conversation">
                <a href="?conversation_id=<?php echo $conversation['id_conversation']; ?>">
                    <div class="conversation-info">
                        <div class="conversation-name"><?php echo getConversationName($conversation['id_conversation']); ?></div>
                        <div class="last-message"><?php echo $lastMessage; ?></div>
                        <div class="last-sender"><?php echo $lastSender; ?></div>
                        <div class="last-datetime"><?php echo $lastDateTime; ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="message-container">
        <h2><?php echo getConversationName($_GET['conversation_id']); ?></h2>
        <div class="message-list">
            <?php foreach ($messages as $message): ?>
                <?php
                $class = ($message['id_user'] == $_SESSION['id']) ? "sent" : "received";
                ?>
                <div class="message <?php echo $class; ?>">
                    <span class="sender"><?php echo $message['first_name'] . ' ' . $message['last_name']; ?>:</span>
                    <br>
                    <?php echo $message['message']; ?>
                    <br>
                    <span class="timestamp"><?php echo date('Y-m-d H:i', strtotime($message['date'])); ?></span>
                    <?php if (!empty($message['filepath'])): ?>
                        <img class="image" src="uploads/<?php echo $message['filepath']; ?>" alt="Image">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="bottom-container">
        <form method="post" class="form-chat" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'] . '?conversation_id=' . $_GET['conversation_id']; ?>">
            <label for="message">Message:</label>
            <textarea name="message" required rows="3" cols="50"></textarea>
            <label for="image">Image or GIF:</label>
            <input type="file" name="image">
            <button type="submit" class="button">Send</button>
        </form>
        <form method="post" class="form-chat"  enctype="multipart/form-data" action="logout.php">
        <button type="submit" class="button" name="logout">Log Out</button>
        </form> 
</div>
</body>
</html>
