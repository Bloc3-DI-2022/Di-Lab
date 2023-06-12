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

$group_id = $_GET['id'] ?? 0;

// Retrieve the group details from the database based on the group ID
$sql_group = "SELECT * FROM `group` WHERE id = ?";
$stmt_group = mysqli_prepare($conn, $sql_group);
mysqli_stmt_bind_param($stmt_group, "i", $group_id);
mysqli_stmt_execute($stmt_group);
$result_group = mysqli_stmt_get_result($stmt_group);

if ($result_group && mysqli_num_rows($result_group) > 0) {
    $group = mysqli_fetch_assoc($result_group);
} else {
    // Handle the case when the group is not found
    // For example, display an error message or redirect to an error page
}

// Retrieve the list of users associated with the group from the database
$sql_users = "SELECT user.id, user.first_name, user.last_name
              FROM user
              INNER JOIN group_user ON user.id = group_user.id_user
              WHERE group_user.id_group = ?";
$stmt_users = mysqli_prepare($conn, $sql_users);
mysqli_stmt_bind_param($stmt_users, "i", $group_id);
mysqli_stmt_execute($stmt_users);
$result_users = mysqli_stmt_get_result($stmt_users);

if ($result_users) {
    $users = mysqli_fetch_all($result_users, MYSQLI_ASSOC);
    mysqli_free_result($result_users);
} else {
    // Handle the SQL error
    echo "SQL Error: " . mysqli_error($conn);
}

// Retrieve the list of users not associated with the group from the database
$sql_all_users = "SELECT id, first_name, last_name FROM user WHERE id NOT IN (SELECT id_user FROM group_user WHERE id_group = ?)";
$stmt_all_users = mysqli_prepare($conn, $sql_all_users);
mysqli_stmt_bind_param($stmt_all_users, "i", $group_id);
mysqli_stmt_execute($stmt_all_users);
$result_all_users = mysqli_stmt_get_result($stmt_all_users);

if ($result_all_users) {
    $all_users = mysqli_fetch_all($result_all_users, MYSQLI_ASSOC);
    mysqli_free_result($result_all_users);
} else {
    // Handle the SQL error
    echo "SQL Error: " . mysqli_error($conn);
}

// Retrieve the list of projects associated with the group from the database
$sql_projects = "SELECT * FROM project WHERE group_id = ?";
$stmt_projects = mysqli_prepare($conn, $sql_projects);
mysqli_stmt_bind_param($stmt_projects, "i", $group_id);
mysqli_stmt_execute($stmt_projects);
$result_projects = mysqli_stmt_get_result($stmt_projects);

if ($result_projects) {
    $projects = mysqli_fetch_all($result_projects, MYSQLI_ASSOC);
    mysqli_free_result($result_projects);
} else {
    // Handle the SQL error
    echo "SQL Error: " . mysqli_error($conn);
}

// Check if a conversation already exists for the group
$sql_check_conversation = "SELECT id FROM conversation WHERE id_group = ? AND is_group_conversation = 1 AND type = 'group'";    
$stmt_check_conversation = mysqli_prepare($conn, $sql_check_conversation);
mysqli_stmt_bind_param($stmt_check_conversation, "i", $group_id);
mysqli_stmt_execute($stmt_check_conversation);
$result_check_conversation = mysqli_stmt_get_result($stmt_check_conversation);

$conversation_exists = ($result_check_conversation && mysqli_num_rows($result_check_conversation) > 0);
$conversation_button_text = $conversation_exists ? "Open Conversation" : "Create Conversation";

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Group</title>
    <meta charset="utf-8">
    <link rel="stylesheet" media="screen" href="edit_group.css">
</head>
<body>
    <div class="nav">
        <?php include("header.php"); ?>
    </div>
    <div class="container">
        <h1>Edit Group</h1>

        <!-- Display group details form -->
        <form method="POST" action="update_group.php">
            <!-- Display input fields for group details -->
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $group['name'] ?? ''; ?>" required>

            <!-- Save and Discard buttons -->
            <div class="save-discard-buttons">
                <button type="submit" class="save-btn">Save</button>
                <a href="edit_group.php?id=<?php echo $group_id; ?>" class="discard-link">Discard</a>
            </div>
        </form>

        <!-- Display list of users associated with the group -->
        <h2>Users Associated with the Group</h2>
        <?php if ($users) : ?>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['first_name']; ?></td>
                            <td><?php echo $user['last_name']; ?></td>
                            <td>
                                <form method="post" action="remove_user_from_group.php">
                                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="remove-user-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No users associated with the group.</p>
        <?php endif; ?>

        <!-- Display list of users not associated with the group -->
        <h2>Users Not Associated with the Group</h2>
        <?php if ($all_users) : ?>
            <div class="recherche-inputs">
                <input type="text" id="search-user" placeholder="Search User" />
            </div>
            <table id="all-users-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Add</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $user) : ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['first_name']; ?></td>
                            <td><?php echo $user['last_name']; ?></td>
                            <td><button class="add-user-btn" data-user-id="<?php echo $user['id']; ?>"><img src="arrow-icon.png" alt="Add User" /></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>All users are associated with the group.</p>
        <?php endif; ?>

        <div class="group-projects">
            <h2>Projects</h2>
            <?php if (isset($projects) && count($projects) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Project ID</th>
                            <th>Name</th>
                            <th>Owner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo $project['id']; ?></td>
                                <td><?php echo $project['name']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No projects associated with the group.</p>
            <?php endif; ?>
        </div>

        <!-- Create/Open Conversation Button -->
        <?php if ($conversation_exists) : ?>
            <a href="chat.php?group_id=<?php echo $group_id; ?>" class="open-conversation-btn"><?php echo $conversation_button_text; ?></a>
        <?php else : ?>
            <form method="POST" action="create_conversation.php">
                <input type="hidden" name="type" value="group">
                <input type="hidden" name="id" value="<?php echo $group_id; ?>">
                <input type="hidden" name="name" value="<?php echo $group['name']; ?>">
                <button type="submit" class="create-conversation-btn"><?php echo $conversation_button_text; ?></button>
            </form>
        <?php endif; ?>
    </div>
    <script src="TableFilter.min.js" defer></script>
    <script src="TableFilter.js" defer></script>
    <script>
        // Add event listener to search input
        document.getElementById("search-user").addEventListener("input", function() {
            var searchText = this.value.toLowerCase();
            var rows = document.querySelectorAll("#all-users-table tbody tr");

            rows.forEach(function(row) {
                var userId = row.querySelector("td:nth-child(1)").innerText.toLowerCase();
                var firstName = row.querySelector("td:nth-child(2)").innerText.toLowerCase();
                var lastName = row.querySelector("td:nth-child(3)").innerText.toLowerCase();
                var showRow = userId.includes(searchText) || firstName.includes(searchText) || lastName.includes(searchText);
                row.style.display = showRow ? "table-row" : "none";
            });
        });

        // Add event listener to add user button
        var addButtons = document.getElementsByClassName("add-user-btn");
        Array.from(addButtons).forEach(function(button) {
            button.addEventListener("click", function(event) {
                event.preventDefault(); // Prevent the default form submission

                var userId = this.getAttribute("data-user-id");
                var groupId = <?php echo $group_id; ?>; // Retrieve the group ID from PHP variable

                // Create a form element dynamically
                var form = document.createElement("form");
                form.action = "add_user_to_group.php";
                form.method = "POST";

                // Create input fields for group ID and user ID
                var groupIdField = document.createElement("input");
                groupIdField.type = "hidden";
                groupIdField.name = "group_id";
                groupIdField.value = groupId;
                form.appendChild(groupIdField);

                var userIdField = document.createElement("input");
                userIdField.type = "hidden";
                userIdField.name = "user_id";
                userIdField.value = userId;
                form.appendChild(userIdField);

                // Submit the form
                document.body.appendChild(form);
                form.submit();
            });
        });
    </script>
</body>
</html>
