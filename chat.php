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



 <!-- Gestion screen -->
 <div class="flex flex-col h-screen justify-between">
<!-- Bloc Navigation -->
<?php get_include("header.php"); ?> 



<div class="container-chat container md:container md:mx-auto flex">



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

    

    <!-- This is an example component -->
<div class="container mx-auto shadow-lg rounded-lg">
        <!-- headaer -->
    <div class="px-5 py-5 flex justify-between items-center bg-white border-b-2">
      <div class="font-semibold text-2xl">DiLab Chat</div>
      <div class="w-1/2">
        <input
          type="text"
          name=""
          id=""
          placeholder="Rechercher dans le Chat"
          class="rounded-2xl bg-gray-100 py-3 px-5 w-full"
        />
      </div>
      <div
        class="h-12 w-12 p-2 bg-yellow-500 rounded-full text-white font-semibold flex items-center justify-center"
      >
        DL
      </div>
    </div>
    <!-- end header -->



    <!-- Chatting -->
    <div class="flex flex-row justify-between bg-white">
      <!-- chat list -->
      <div class="flex flex-col w-2/5 border-r-2 overflow-y-auto">
        <!-- search compt -->
        <div class="border-b-2 py-4 px-2">
          <input
            type="text"
            placeholder="Rechercher dans les conversations"
            class="py-2 px-2 border-2 border-gray-200 rounded-2xl w-full"
          />
        </div>
        <!-- end search compt -->
        <!-- user list -->
        <div class="sidebar">
        <h2>Conversations</h2>


        <?php foreach ($conversations as $conversation): ?>
            <?php
                $lastMessage = getLastMessage($conversation['id_conversation']);
                $lastSender = getLastSender($conversation['id_conversation']);
                $lastDateTime = getLastDateTime($conversation['id_conversation']);
            ?>
            <div class="flex flex-row py-4 px-2 justify-center items-center border-b-2 conversation">
                <a href="?conversation_id=<?php echo $conversation['id_conversation']; ?>">
                    <div class="conversation-info w-full">
                        <div class="conversation-name"><?php echo getConversationName($conversation['id_conversation']); ?></div>
                        <div class="text-gray-500"><?php echo $lastMessage; ?></div>
                        <div class="last-sender text-lg font-semibold"><?php echo $lastSender; ?></div>
                        <div class="last-datetime"><?php echo $lastDateTime; ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>




     


       
        <!-- end user list -->
      </div>


      <!-- end chat list -->
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
      
      <!-- message -->
      <div class="w-full px-5 flex flex-col justify-between">
        <div class="flex flex-col mt-5">
          <div class="flex justify-end mb-4">
            <div
              class="mr-2 py-3 px-4 bg-blue-400 rounded-bl-3xl rounded-tl-3xl rounded-tr-xl text-white"
            >
              Welcome to group everyone !
            </div>
            <img
              src="https://source.unsplash.com/vpOeXr5wmR4/600x600"
              class="object-cover h-8 w-8 rounded-full"
              alt=""
            />
          </div>
          <div class="flex justify-start mb-4">
            <img
              src="https://source.unsplash.com/vpOeXr5wmR4/600x600"
              class="object-cover h-8 w-8 rounded-full"
              alt=""
            />
            <div
              class="ml-2 py-3 px-4 bg-gray-400 rounded-br-3xl rounded-tr-3xl rounded-tl-xl text-white"
            >
              Lorem ipsum dolor sit amet consectetur adipisicing elit. Quaerat
              at praesentium, aut ullam delectus odio error sit rem. Architecto
              nulla doloribus laborum illo rem enim dolor odio saepe,
              consequatur quas?
            </div>
          </div>
          <div class="flex justify-end mb-4">
            <div>
              <div
                class="mr-2 py-3 px-4 bg-blue-400 rounded-bl-3xl rounded-tl-3xl rounded-tr-xl text-white"
              >
                Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                Magnam, repudiandae.
              </div>

              <div
                class="mt-4 mr-2 py-3 px-4 bg-blue-400 rounded-bl-3xl rounded-tl-3xl rounded-tr-xl text-white"
              >
                Lorem ipsum dolor sit amet consectetur adipisicing elit.
                Debitis, reiciendis!
              </div>
            </div>
            <img
              src="https://source.unsplash.com/vpOeXr5wmR4/600x600"
              class="object-cover h-8 w-8 rounded-full"
              alt=""
            />
          </div>
          <div class="flex justify-start mb-4">
            <img
              src="https://source.unsplash.com/vpOeXr5wmR4/600x600"
              class="object-cover h-8 w-8 rounded-full"
              alt=""
            />
            <div
              class="ml-2 py-3 px-4 bg-gray-400 rounded-br-3xl rounded-tr-3xl rounded-tl-xl text-white"
            >
              happy holiday guys!
            </div>
          </div>
        </div>
        <div class="py-5">
          <input
            class="w-full bg-gray-300 py-5 px-3 rounded-xl"
            type="text"
            placeholder="type your message here..."
          />
        </div>
      </div>
      <!-- end message -->


      <div class="w-2/5 border-l-2 px-5">
        <div class="flex flex-col">
          <div class="font-semibold text-xl py-4">Mern Stack Group</div>
          <img
            src="https://source.unsplash.com/L2cxSuKWbpo/600x600"
            class="object-cover rounded-xl h-64"
            alt=""
          />
          <div class="font-semibold py-4">Created 22 Sep 2021</div>
          <div class="font-light">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Deserunt,
            perspiciatis!
          </div>
          </div>
        </div>
      </div>

      
    </div>
</div>



<?php include("footer.php"); ?>
</div>
</body>
</html>
