<?php
require 'db_connection.php';
// Get user id from URL
$user_id = $_GET['id'];

// Get user details
$sql = "SELECT * FROM user WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Get user types
$sql = "SELECT * FROM userTypes";
$result = $conn->query($sql);
$userTypes = $result->fetch_all(MYSQLI_ASSOC);

// Get promos
$sql = "SELECT * FROM promo";
$result = $conn->query($sql);
$promos = $result->fetch_all(MYSQLI_ASSOC);

// Get promos associated with user
$sql = "SELECT promo.* FROM promo INNER JOIN promo_user ON promo.id = promo_user.id_promo WHERE promo_user.id_user = $user_id";
$result = $conn->query($sql);
$userPromos = $result->fetch_all(MYSQLI_ASSOC);
?>
<DOCTYPE html>
    <html>
    <head>
        <title>Edit User</title>
        <link rel='stylesheet' type='text/css' href='edit_user.css'/>
        <meta charset="UTF-8">
    </head>
    <body>
    <h1>Edit User</h1>
    <form action='update_user.php' method='post'>
        <input type='hidden' name='id' value='<?= $user['id'] ?>'/>
        <label for='first_name'>First Name:</label><br>
        <input type='text' name='first_name' value='<?= $user['first_name'] ?>'/><br>
        <label for='last_name'>Last Name:</label><br>
        <input type='text' name='last_name' value='<?= $user['last_name'] ?>'/><br>
        <label for='email'>Email:</label><br>
        <input type='email' name='email' value='<?= $user['email'] ?>'/><br>
      
        <label for='user_type_id'>User Type:</label><br>
        <select name='user_type_id'>
            <?php foreach ($userTypes as $userType): ?>
                <option value='<?= $userType['id'] ?>' <?= $userType['id'] == $user['user_type_id'] ? "selected" : "" ?>><?= $userType['type'] ?></option>
            <?php endforeach; ?>
        </select><br>
        <label for='promo_id'>Add Promo:</label><br>
        <select name='promo_id'>
            <?php foreach ($promos as $promo): ?>
                <option value='<?= $promo['id'] ?>'><?= $promo['name'] ?></option>
            <?php endforeach; ?>
        </select><br>
        <h2>Associated Promos</h2>
        <table>
            <tr>
                <th>Promo Name</th>
                <th>Remove</th>
            </tr>
            <?php foreach ($userPromos as $promo): ?>
                <tr>
                    <td><?= $promo['name'] ?></td>
                    <td>
                        <button onclick="location.href='remove_promo.php?user_id=<?= $user['id'] ?>&promo_id=<?= $promo['id'] ?>'">
                            Remove
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <input type='submit' value='Update User'/>
    </form>
    </body>
    </html>