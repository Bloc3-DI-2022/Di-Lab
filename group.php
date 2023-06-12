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

?>

<!DOCTYPE html>
<html>
<head>
    <title>Group List</title>
    <meta charset="utf-8">
    <link rel="stylesheet" media="screen" href="group.css">
</head>
<body>
    <div class="nav">
        <?php include("header.php"); ?> 
    </div>
    <h1>Group List</h1>
    <div id="group-cards"> <!-- Add this line to wrap all group cards -->
    <?php foreach ($groups as $group): ?>
        <div class="group-card"> <!-- Add this line to represent a group card -->
            <div class="group-info">
                <div class="group-name"><?php echo $group['name']; ?></div>
                <div class="group-owner">Created by: <?php echo $group['owner']; ?></div>
                <div class="group-creation-date">Creation Date: <?php echo $group['creation_date']; ?></div>
                <div class="group-project-count">Number of Projects: <?php echo $group['project_count']; ?></div>
                <div class="group-member-count">Number of Members: <?php echo $group['member_count']; ?></div> <!-- Display member count -->
                <div class="group-actions">
                    <a href="edit_group.php?id=<?php echo $group['id']; ?>" class="edit-group-btn">Edit</a>
                </div>
            </div>
        </div> <!-- Close group card div -->
    <?php endforeach; ?>
    </div> <!-- Close group cards div -->
</body>
</html>

</html>
