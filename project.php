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

// Fetch all projects for the logged-in user with the count of projects
$sql = "SELECT *
FROM project_user
INNER JOIN project ON project_user.project_id = project.id
INNER JOIN user ON user.id = project_user.user_id
WHERE project_user.user_id = ?
  AND (
    (user.user_type_id IN (1) AND project.share_with_intervenant = 1)
    OR (user.user_type_id IN (3) AND project.share_with_pilot = 1)
    OR (user.user_type_id = 2)
  )";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    die('Error: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$projects = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
include("fonction.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Project List</title>
    <meta charset="utf-8">
    <link rel="stylesheet" media="screen" href="group.css">
</head>
<body>


<div class="flex flex-col h-screen justify-between">
<!-- Bloc Navigation -->
<?php get_include("header.php"); ?> 



<div class="container-chat container md:container md:mx-auto flex flex-col  h-4/5 justify-items-center justify-center items-center">
<h1 class="font-semibold text-3xl">Liste des projets</h1>
    
    

    <div id="group-cards " class="flex flex-wrap flex-row h-4/5 overflow-y-auto border-2 border-gray-200 p-3 justify-items-center justify-center "> <!-- Add this line to wrap all group cards -->
    <?php foreach ($projects as $project): ?>
        <div class="group-card flex flex-col content-center justify-items-center justify-center" > <!-- Add this line to represent a group card -->
            <div class="group-info items-center border-2 border-gray-200 p-5 m-3 flex flex-col bg-white p-10 rounded-lg shadow-md" >
            <h1 class="text-xl font-bold"><?php echo $project['name']; ?></h1>
            <div class="mt-4 mb-8">
      
      
    </div>
    
    
    <h2 class="tracking-wide">
    Share with Pilot: <?php echo $project['share_with_pilot']; ?>
      <br />
      Share with Intervenant: <?php echo $project['share_with_intervenant']; ?></br>
      

    </h2>
    <button class="w-full bg-orange-400 py-3 px-8 mt-4 rounded text-sm font-semibold hover:bg-opacity-75"><a href="edit_project.php?id=<?php echo $project['id']; ?>" class="edit-project-btn">Edit</a></button>
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
