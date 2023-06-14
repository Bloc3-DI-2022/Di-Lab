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

// Fetch all groups for the logged-in user with the count of projects
$sql = "SELECT g.id, g.name, g.creation_date, CONCAT(u.first_name, ' ', u.last_name) AS owner,
        (SELECT COUNT(*) FROM project WHERE group_id = g.id) AS project_count,
        (SELECT COUNT(*) FROM group_user WHERE id_group = g.id) AS member_count
        FROM `group` AS g
        INNER JOIN user AS u ON g.creator_user_id = u.id
        INNER JOIN group_user AS gu ON g.id = gu.id_group
        WHERE gu.id_user = ?
        GROUP BY g.id";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die('Error: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$groups = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
include("fonction.php");
?><html>
<head>
    <title>Group List</title>
    <meta charset="utf-8">
    <link rel="stylesheet" media="screen" href="group.css">
</head>
<body>
   
<div class="flex flex-col h-screen justify-between">
<!-- Bloc Navigation -->
<?php get_include("header.php"); ?> 



<div class="container-chat container md:container md:mx-auto flex flex-wrap flex-row h-4/5 justify-items-center justify-center">
<h1 class="font-semibold text-3xl">Group List</h1>
    
    

    <div id="group-cards " class="flex flex-wrap flex-row h-4/5 overflow-y-auto border-2 border-gray-200 p-3 justify-items-center justify-center "> <!-- Add this line to wrap all group cards -->
    <?php foreach ($groups as $group): ?>
        <div class="group-card flex flex-col content-center justify-items-center justify-center" > <!-- Add this line to represent a group card -->
            <div class="group-info items-center border-2 border-gray-200 p-5 m-3 flex flex-col bg-white p-10 rounded-lg shadow-md" >
            <h1 class="text-xl font-bold"><?php echo $group['name']; ?></h1>
            <div class="mt-4 mb-8">
      
      
    </div>
    
    
    <h2 class="tracking-wide">
    Created by: <?php echo $group['owner']; ?>
      <br />
      Creation Date: <?php echo $group['creation_date']; ?></br>
      Number of Projects: <?php echo $group['project_count']; ?><br/>
      Number of Members: <?php echo $group['member_count']; ?><br/>

    </h2>
    <button class="w-full bg-orange-400 py-3 px-8 mt-4 rounded text-sm font-semibold hover:bg-opacity-75"><a href="edit_group.php?id=<?php echo $group['id']; ?>" class="edit-group-btn">Edit</a></button>
  </div>






               
            </div>
         <!-- Close group card div -->
    <?php endforeach; ?>
    </div>
    </div> <!-- Close group cards div -->

   

   



<?php include("footer.php"); ?>
</div>
</body>
</html>

</html>
