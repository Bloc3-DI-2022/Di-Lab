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

?>

<!DOCTYPE html>
<html>
<head>
    <title>Project List</title>
    <meta charset="utf-8">
    <link rel="stylesheet" media="screen" href="group.css">
</head>
<body>
    <div class="nav">
        <?php include("header.php"); ?> 
    </div>
    <h1>Project List</h1>
    <div id="project-cards"> <!-- Add this line to wrap all project cards -->
    <?php foreach ($projects as $project): ?>
        <div class="project-card"> <!-- Add this line to represent a project card -->
            <div class="project-info">
                <div class="project-name"><?php echo $project['name']; ?></div>
                <div class="project-share-pilot">Share with Pilot: <?php echo $project['share_with_pilot']; ?></div>
                <div class="project-share-intervenant">Share with Intervenant: <?php echo $project['share_with_intervenant']; ?></div>
                <!-- Add more project information here as needed -->
                <div class="project-actions">
                    <a href="edit_project.php?id=<?php echo $project['id']; ?>" class="edit-project-btn">Edit</a>
                </div>
            </div>
        </div> <!-- Close project card div -->
    <?php endforeach; ?>
    </div> <!-- Close project cards div -->
</body>
</html>
