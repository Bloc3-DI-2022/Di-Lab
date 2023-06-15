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



<div class="container-chat container md:container md:mx-auto flex h-4/5">


    

    

    <!-- This is an example component -->
<div class="container mx-auto shadow-lg rounded-lg">
        <!-- headaer -->
    <div class="px-5 py-5 flex justify-between items-center bg-white border-b-2">
      <div class="font-semibold text-3xl">Chat</div>
      <div class="w-1/2">
        <input
          type="text"
          name=""
          id=""
          placeholder="Rechercher dans le Chat"
          class="rounded-2xl bg-gray-100 py-3 px-5 w-full"
        />
      </div>
   
    </div>
    <!-- end header -->



    <!-- Chatting -->
    <div class="flex flex-row justify-between bg-white h-5/6">
      <!-- chat list -->
      <div class="flex flex-col w-1/4">
      <div class="flex flex-col  border-r-2 overflow-y-auto overflow-x-hidden h-4/5">
        <!-- search compt -->
        <div class="border-b-2 py-4 px-2">
          <input
            type="text"
            placeholder="Rechercher dans les conversations"
            class="py-2 px-2 border-2 border-gray-200 rounded-2xl w-screen"
          />
        </div>
        <!-- end search compt -->
        <!-- user list -->
        <div class="sidebar w-full overflow-auto max-h-fit">
        <h2>Conversations</h2>


        <?php foreach ($conversations as $conversation): ?>
            <?php
                $lastMessage = getLastMessage($conversation['id_conversation']);
                $lastSender = getLastSender($conversation['id_conversation']);
                $lastDateTime = getLastDateTime($conversation['id_conversation']);
            ?>
            <div class="flex flex-row py-4 px-2 justify-center items-center border-b-2 conversation">
                <a class="w-full" href="?conversation_id=<?php echo $conversation['id_conversation']; ?>">
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

<div class="create-promo-button flex justify-center md:div md:mx-auto flex pt-3 pb-3">
      <form method="post" action="create_promo.php" class="flex  justify-center justify-items-center md:form md:mx-auto flex">
        <p class="mr-4">Créer un chat</p>
        <button type="submit" class="create-promo-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>
</button> 
      </form>
    </div>
      <!-- end chat list -->
      </div>

     
    <div id="messagesContainer">
        <!-- Existing message elements -->
    </div>
    
    <div class="flex flex-col h-5/6 overflow-y-auto">
    <div id="messagesContainer">
        <!-- Existing message elements -->
    </div>
</div>



      <!-- message -->
      <div class="w-full px-5 flex flex-col ">

      <div class="flex flex-col h-5/6 overflow-y-auto">
        
          <div class="flex flex-col justify-evenly mb-4">
          <?php foreach ($messages as $message): ?>
                <?php
                $class = ($message['id_user'] == $_SESSION['id']) ? "sent" : "received";
                ?>
            <div class="h-26 mb-4 mr-2 py-3 px-4  <?php echo ($class === 'received') ? 'bg-green-400  rounded-br-3xl rounded-bl-sm place-self-start rounded-tl-3xl rounded-tr-xl ' : ' place-self-end bg-blue-400'; ?>  rounded-bl-3xl rounded-tl-3xl rounded-tr-xl text-white">

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





      <!-- <button>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
              </button>

              <input type="text" placeholder="Message"
                class="block w-full py-2 pl-4 mx-3 bg-gray-100 rounded-full outline-none focus:text-gray-700"
                name="message" required />
              <button>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
              </button>
              <button type="submit">
                <svg class="w-5 h-5 text-gray-500 origin-center transform rotate-90" xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20" fill="currentColor">
                  <path
                    d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                </svg>
              </button> -->



<div class="">


      <form method="post" class="mt-2 form-chat flex items-center justify-between  border-t border-gray-300 " enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'] . '?conversation_id=' . $_GET['conversation_id']; ?>">
           
            <textarea class="block w-full py-2 pl-4 mx-3 bg-gray-100 rounded-lg outline-none focus:text-gray-700" name="message" required rows="3" cols="50"></textarea>
          
                 <div class="flex flex-col flex items-center justify-between">     
            <input type="file" class="block w-full text-sm text-gray-500 
      file:mr-4 file:py-2 file:px-4
      file:rounded-md file:border-0
      file:text-sm file:font-semibold
      file:bg-blue-500 file:text-white
      hover:file:bg-blue-600"  name="image">
            <button type="submit" class=" w-12 hover:bg-gray-700 text-white font-bold py-2 mt-2 px-4 border border-gray-700 rounded"> <svg class="w-5 h-5 text-gray-500 origin-center transform rotate-90" xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 20 20" fill="currentColor">
                  <path
                    d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                </svg></button>
                </div>  
      </form>
</div>        
    </div>


        </div>
       
      </div>
      <!-- end message -->
    
      </div>

   



<?php include("footer.php"); ?>
</div>
<script>
    // Function to scroll to the bottom of the messages container
    function scrollMessagesContainer() {
        var messagesContainer = document.getElementById("messagesContainer");
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Call the scroll function when the page loads
    window.onload = scrollMessagesContainer;

    // Call the scroll function whenever new messages are added
    var form = document.querySelector("form");
    form.addEventListener("submit", scrollMessagesContainer);
</script>

</body>
</html>


