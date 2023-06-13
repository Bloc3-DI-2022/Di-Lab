<?php 
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'chat_db_2');
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}


require_once 'db_connection.php';

//redirect to login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: index.php");
  exit;
}

mysqli_close($conn); 
include("fonction.php");
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

?><html>
  
<title>Chat DiLAB</title>
<meta charset="utf-8">
<link rel="stylesheet" media="screen" href="accueil.css"/>

<body>
 <!-- Bloc Navigation -->
 <div class="flex flex-col h-screen justify-between">


    <?php include("header.php"); ?> 

    <div class="flex flex-wrap w-full justify-evenly">

      <div class="border-2 p-5 basis-1/4 flex  ">
            <div class="w-5/6 flex flex-col justify-center">
              <h1 class="pb-3 mb-2 text-3xl ">Conversation</h1>
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
      
        <div class="message-container">
          
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
        </div> 
        <div class="place-self-center justify-center">

        <a href="chat.php?conversation_id=0" class="py-2 ">Se rendre sur le Chat
        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-9 pt-5 ">
  <path d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
        </a>
        </div>
      </div>

      <div class="flex flex-col space-between basis-1/3">

      <div class="">
        <h1>Messages importants</h1>
      </div>
      <div>
        <h1>derniers projets</h1>
      </div>
      </div>
     
    </div>


    <?php include("footer.php"); ?>

    </div>
</body>
</html>