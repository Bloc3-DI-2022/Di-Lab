<?php 
session_start();
require_once 'db_connection.php';

//redirect to login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: index.php");
  exit;
}

$lastThreeMessages = getLastThreeMessages($_SESSION['id']);

function getLastThreeMessages($user_id) {
    global $conn;
    $sql = "SELECT conversation.name AS conversation_name, message.message, user.first_name, user.last_name, message.date, conversation.id AS conversation_id 
            FROM conversation_user 
            INNER JOIN conversation ON conversation.id = conversation_user.id_conversation 
            INNER JOIN message ON message.id_conversation = conversation_user.id_conversation 
            INNER JOIN user ON user.id = message.id_user 
            WHERE conversation_user.id_user = ? 
            ORDER BY message.date DESC 
            LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $messages;
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


$sql = "SELECT admin_chat.*, promo.name AS promo_name, user.first_name AS pilot_name
        FROM admin_chat
        JOIN promo_user ON admin_chat.id_promo = promo_user.id_promo
        JOIN promo AS promo ON admin_chat.id_promo = promo.id
        JOIN user AS user ON admin_chat.id_pilot = user.id
        WHERE promo_user.id_user = {$_SESSION['id']}
        limit 3";


$result = mysqli_query($conn, $sql);
if (!$result) {
    echo 'Erreur de requête : ' . mysqli_error($conn);
}$admin_messages = mysqli_fetch_all($result, MYSQLI_ASSOC);




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
            <div class="last-three-messages space-y-4">
                <?php foreach ($lastThreeMessages as $message): ?>
                    <a href="chat.php?conversation_id=<?php echo $message['conversation_id']; ?>" class="block bg-white shadow p-4 rounded-md text-black no-underline hover:bg-blue-100">
                        <h3 class="conversation-name text-amber-300 font-semibold mb-4  "><?php echo $message['conversation_name']; ?></h3>
                        <p class="message-text text-gray-700 mb-1"><?php echo $message['message']; ?></p>
                        <p class="sender text-sm text-gray-500 mb-1"><?php echo $message['first_name'] . ' ' . $message['last_name']; ?></p>
                        <p class="datetime text-xs text-gray-400"><?php echo $message['date']; ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
      </div>

      <div class="flex flex-col space-between basis-1/3">

      <div class="">
          <div class="w-full px-5 flex flex-col ">
              <h1 class="font-bold text-2xl mb-4">Messages importants</h1>
              <div class="flex flex-col space-y-4">
                  <?php foreach ($admin_messages as $admin_message): ?>
                      <div class="h-26 mb-4 py-3 px-4 bg-gray-400 rounded-bl-3xl rounded-tl-3xl rounded-tr-xl text-white">
                          <h3 class="font-bold"><?php echo $admin_message['message']; ?></h3>
                          <p class="text-sm">Promo : <?php echo $admin_message['promo_name']; ?></p>
                          <p class="text-sm">Sended by : <?php echo $admin_message['pilot_name']; ?></p>
                          <p class="text-sm">Date: <?php echo $admin_message['date']; ?></p>
                          <p class="text-sm">Priority: <?php echo $admin_message['priority']; ?></p>
                      </div>
                  <?php endforeach; ?>
              </div>
          </div>
      </div>
    
      </div>
     
    </div>


    <?php include("footer.php"); ?>

    </div>
</body>
</html>