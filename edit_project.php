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

$project_id = $_GET['id'] ?? 0;

// Retrieve the project details from the database based on the project ID
$sql_project = "SELECT * FROM project WHERE id = ?";
$stmt_project = mysqli_prepare($conn, $sql_project);
mysqli_stmt_bind_param($stmt_project, "i", $project_id);
mysqli_stmt_execute($stmt_project);
$result_project = mysqli_stmt_get_result($stmt_project);

if ($result_project && mysqli_num_rows($result_project) > 0) {
    $project = mysqli_fetch_assoc($result_project);
} else {
    // Handle the case when the project is not found
    // For example, display an error message or redirect to an error page
}

// Retrieve the list of users associated with the project from the database
$sql_users = "SELECT user.id, user.first_name, user.last_name
              FROM project_user
              INNER JOIN user ON user.id = project_user.user_id
              WHERE project_user.project_id = ?";
$stmt_users = mysqli_prepare($conn, $sql_users);
mysqli_stmt_bind_param($stmt_users, "i", $project_id);
mysqli_stmt_execute($stmt_users);
$result_users = mysqli_stmt_get_result($stmt_users);

if ($result_users) {
    $users = mysqli_fetch_all($result_users, MYSQLI_ASSOC);
    mysqli_free_result($result_users);
} else {
    // Handle the SQL error
    echo "SQL Error: " . mysqli_error($conn);
}

// Retrieve the list of all users from the database
$sql_all_users = "SELECT id, first_name, last_name FROM user";
$result_all_users = mysqli_query($conn, $sql_all_users);

if ($result_all_users) {
    $all_users = mysqli_fetch_all($result_all_users, MYSQLI_ASSOC);
    mysqli_free_result($result_all_users);
} else {
    // Handle the SQL error
    echo "SQL Error: " . mysqli_error($conn);
}

// Filter out the users who are already associated with the project
$not_associated_users = array_filter($all_users, function($user) use ($users) {
    return !in_array($user, $users);
});

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Project</title>
    <meta charset="utf-8">
    <link rel="stylesheet" media="screen" href="edit_project.css">
</head>
<body>
    <div class="nav">
        <?php include("header.php"); ?>
    </div>
    <div class="container">
        <h1>Edit Project</h1>

        <!-- Display project details form -->
        <form method="POST" action="update_project.php">
            <!-- Display input fields for project details -->
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $project['name'] ?? ''; ?>" required>
            <label for="share-with-pilot">Share with Pilot:</label>
            <input type="checkbox" id="share-with-pilot" name="share_with_pilot" value="1" <?php echo ($project['share_with_pilot'] ?? '') ? 'checked' : ''; ?>>
            <label for="share-with-intervenant">Share with Intervenant:</label>
            <input type="checkbox" id="share-with-intervenant" name="share_with_intervenant" value="1" <?php echo ($project['share_with_intervenant'] ?? '') ? 'checked' : ''; ?>>

            <!-- Save and Discard buttons -->
            <div class="save-discard-buttons">
                <button type="submit" class="save-btn">Save</button>
                <a href="edit_project.php?id=<?php echo $project_id; ?>" class="discard-link">Discard</a>
            </div>
        </form>

        <!-- Display list of users associated with the project -->
        <h2>Users Associated with the Project</h2>
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
                                <form method="post" action="remove_user_from_project.php">
                                    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="remove-user-btn">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No users associated with the project.</p>
        <?php endif; ?>

        <!-- Display list of users not associated with the project -->
        <h2>Users Not Associated with the Project</h2>
        <?php if ($not_associated_users) : ?>
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
                    <?php foreach ($not_associated_users as $user) : ?>
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
            <p>All users are associated with the project.</p>
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
