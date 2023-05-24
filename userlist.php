<?php
require 'db_connection.php';

$sql = "SELECT user.*, userTypes.type FROM user INNER JOIN userTypes ON user.user_type_id = userTypes.id";
$result = $conn->query($sql);
include("fonction.php");
?>
<Doctype html>
    <html>
    <head>
        <title>Users List</title>
        <meta charset="UTF-8">
        <link rel='stylesheet' type='text/css' href='userlist.css'/>
    </head>
    <body>
    <div class="nav">
    <?php get_include("header.php"); ?> 
    </div>

    <table>
        <tr>
            <th>Is admin</th>
            <th>User Type</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Edit</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class='<?= $row['is_admin'] == 1 ? "is_admin" : "" ?>'>
                        <?php if ($row['is_admin'] == 1): ?>
                            <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'
                                 fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round'
                                 stroke-linejoin='round' class='feather feather-check'>
                                <polyline points='20 6 9 17 4 12'></polyline>
                            </svg>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['type'] ?></td>
                    <td><?= $row['first_name'] ?></td>
                    <td><?= $row['last_name'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td class="bouton-edit">
                        <button class="edit-bouton" onclick="location.href='edit_user.php?id=<?= $row['id'] ?>'">Edit</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">0 results</td>
            </tr>
        <?php endif; ?>
    </table>
    </body>
    </html>
