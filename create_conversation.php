<?php
session_start();
require_once 'db_connection.php';

// Redirect to login if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the conversation details from the form
    $type = $_POST['type'];
    $id = $_POST['id'];

    // Set the appropriate column names based on the conversation type
    $id_group = null;
    $id_project = null;
    $id_promo = null;

    if ($type === 'group') {
        $id_group = $id;
    } elseif ($type === 'project') {
        $id_project = $id;
    } elseif ($type === 'promo') {
        $id_promo = $id;
    }

    // Set the conversation name as the type concatenated with the name of the promo, project, or group
    $name = ucfirst($type) . ' - ';

    if ($type === 'group') {
        $sql_get_group_name = "SELECT name FROM `group` WHERE id = ?";
        $stmt_get_group_name = mysqli_prepare($conn, $sql_get_group_name);
        mysqli_stmt_bind_param($stmt_get_group_name, "i", $id_group);
        mysqli_stmt_execute($stmt_get_group_name);
        mysqli_stmt_bind_result($stmt_get_group_name, $group_name);
        mysqli_stmt_fetch($stmt_get_group_name);
        mysqli_stmt_close($stmt_get_group_name);

        $name .= $group_name;
    } elseif ($type === 'project') {
        $sql_get_project_name = "SELECT name FROM project WHERE id = ?";
        $stmt_get_project_name = mysqli_prepare($conn, $sql_get_project_name);
        mysqli_stmt_bind_param($stmt_get_project_name, "i", $id_project);
        mysqli_stmt_execute($stmt_get_project_name);
        mysqli_stmt_bind_result($stmt_get_project_name, $project_name);
        mysqli_stmt_fetch($stmt_get_project_name);
        mysqli_stmt_close($stmt_get_project_name);

        $name .= $project_name;
    } elseif ($type === 'promo') {
        $sql_get_promo_name = "SELECT name FROM promo WHERE id = ?";
        $stmt_get_promo_name = mysqli_prepare($conn, $sql_get_promo_name);
        mysqli_stmt_bind_param($stmt_get_promo_name, "i", $id_promo);
        mysqli_stmt_execute($stmt_get_promo_name);
        mysqli_stmt_bind_result($stmt_get_promo_name, $promo_name);
        mysqli_stmt_fetch($stmt_get_promo_name);
        mysqli_stmt_close($stmt_get_promo_name);

        $name .= $promo_name;
    }

    // Check if a conversation with the same id_group, id_project, or id_promo already exists
    $sql_check_conversation = "SELECT * FROM conversation WHERE (id_group = ? AND is_group_conversation = 1) OR (id_project = ? AND is_project_conversation = 1) OR (id_promo = ? AND is_promo_conversation = 1)";
    $stmt_check_conversation = mysqli_prepare($conn, $sql_check_conversation);
    if (!$stmt_check_conversation) {
        die('Error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_check_conversation, "iii", $id_group, $id_project, $id_promo);
    mysqli_stmt_execute($stmt_check_conversation);
    mysqli_stmt_store_result($stmt_check_conversation);

    if (mysqli_stmt_num_rows($stmt_check_conversation) > 0) {
        // Conversation with the same id_group, id_project, or id_promo already exists
        mysqli_stmt_close($stmt_check_conversation);

        // Redirect back to the appropriate page with an error message
        if ($type === 'promo') {
            header("location: edit_promo.php?id=$id&error=conversation_exists");
        } elseif ($type === 'group') {
            header("location: edit_group.php?id=$id&error=conversation_exists");
        } elseif ($type === 'project') {
            header("location: edit_project.php?id=$id&error=conversation_exists");
        }
        exit;
    }

    mysqli_stmt_close($stmt_check_conversation);

    // Insert the conversation into the conversation table
    $sql_insert_conversation = "INSERT INTO conversation (name, type, is_group_conversation, id_group, is_project_conversation, id_project, is_promo_conversation) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert_conversation = mysqli_prepare($conn, $sql_insert_conversation);
    if (!$stmt_insert_conversation) {
        die('Error: ' . mysqli_error($conn));
    }

    // Set the appropriate boolean values for the conversation type
    $is_group_conversation = $type === 'group' ? 1 : 0;
    $is_project_conversation = $type === 'project' ? 1 : 0;
    $is_promo_conversation = $type === 'promo' ? 1 : 0;

    mysqli_stmt_bind_param($stmt_insert_conversation, "ssiiiii", $name, $type, $is_group_conversation, $id_group, $is_project_conversation, $id_project, $is_promo_conversation);
    mysqli_stmt_execute($stmt_insert_conversation);
    $conversation_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt_insert_conversation);

    // Add entries in the group_user, project_user, or promo_user table
    $sql_get_users = '';
    $stmt_get_users = null;

    if ($type === 'group') {
        $sql_get_users = "SELECT id_user FROM group_user WHERE id_group = ?";
        $stmt_get_users = mysqli_prepare($conn, $sql_get_users);
        mysqli_stmt_bind_param($stmt_get_users, "i", $id);
    } elseif ($type === 'project') {
        $sql_get_users = "SELECT id_user FROM project_user WHERE id_project = ?";
        $stmt_get_users = mysqli_prepare($conn, $sql_get_users);
        mysqli_stmt_bind_param($stmt_get_users, "i", $id);
    } elseif ($type === 'promo') {
        $sql_get_users = "SELECT id_user FROM promo_user WHERE id_promo = ?";
        $stmt_get_users = mysqli_prepare($conn, $sql_get_users);
        mysqli_stmt_bind_param($stmt_get_users, "i", $id);
    }

    if ($stmt_get_users) {
        mysqli_stmt_execute($stmt_get_users);
        mysqli_stmt_bind_result($stmt_get_users, $user_id);

        // Add entries in the conversation_user table
        $sql_add_conversation_user = "INSERT INTO conversation_user (id_conversation, id_user) VALUES (?, ?)";
        $stmt_add_conversation_user = mysqli_prepare($conn, $sql_add_conversation_user);

        if ($stmt_add_conversation_user) {
            while (mysqli_stmt_fetch($stmt_get_users)) {
                mysqli_stmt_bind_param($stmt_add_conversation_user, "ii", $conversation_id, $user_id);
                mysqli_stmt_execute($stmt_add_conversation_user);
            }

            mysqli_stmt_close($stmt_add_conversation_user);
        }

        mysqli_stmt_close($stmt_get_users);
    }

    // Redirect back to the appropriate page with a success message
    if ($type === 'promo') {
        header("location: edit_promo.php?id=$id&success=conversation_created");
    } elseif ($type === 'group') {
        header("location: edit_group.php?id=$id&success=conversation_created");
    } elseif ($type === 'project') {
        header("location: edit_project.php?id=$id&success=conversation_created");
    }
    exit;
}

mysqli_close($conn);
?>
