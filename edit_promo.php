<?php
session_start();

require_once 'db_connection.php';

// Redirect to login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: index.php");
  exit;
}

// Retrieve the promo ID from the URL parameter
$promo_id = $_GET['id'] ?? 0;

// Retrieve the promo details from the database based on the promo ID
$sql_promo = "SELECT * FROM promo WHERE id = $promo_id";
$result_promo = mysqli_query($conn, $sql_promo);

if ($result_promo && mysqli_num_rows($result_promo) > 0) {
  $promo = mysqli_fetch_assoc($result_promo);
} else {
  // Handle the case when the promo is not found
  // For example, display an error message or redirect to an error page
}

// Retrieve the list of pilots from the database
$sql_pilots = "SELECT * FROM user WHERE user_type_id = 3";
$result_pilots = mysqli_query($conn, $sql_pilots);
$pilots = mysqli_fetch_all($result_pilots, MYSQLI_ASSOC);
mysqli_free_result($result_pilots);

// Retrieve the list of users associated with the promo from the database
$sql_users = "SELECT user.id, user.first_name, user.last_name
              FROM user
              INNER JOIN promo_user ON user.id = promo_user.id_user
              WHERE promo_user.id_promo = $promo_id";
$result_users = mysqli_query($conn, $sql_users);

if ($result_users) {
  $users = mysqli_fetch_all($result_users, MYSQLI_ASSOC);
  mysqli_free_result($result_users);
} else {
  // Handle the SQL error
  echo "SQL Error: " . mysqli_error($conn);
}

// Retrieve the list of users not associated with the promo from the database
$sql_all_users = "SELECT id, first_name, last_name FROM user WHERE id NOT IN (SELECT id_user FROM promo_user WHERE id_promo = $promo_id)";
$result_all_users = mysqli_query($conn, $sql_all_users);

if ($result_all_users) {
  $all_users = mysqli_fetch_all($result_all_users, MYSQLI_ASSOC);
  mysqli_free_result($result_all_users);
} else {
  // Handle the SQL error
  echo "SQL Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<title>Chat DiLAB</title>
<meta charset="utf-8">
<link rel="stylesheet" media="screen" href="edit_promo.css" />

<body>
  <!-- Bloc Navigation -->
  <div class="container" id="bloc-nav">
    <nav class="barre-nav">
      <div class="navbar-header">
        <a class="renvoi-accueil" href="accueil.php">Di-Lab</a>
      </div>

      <div class="collapse navbar-collapse navbar-1">
        <ul class="site-navigation nav">
          <li><a href="#">Promos</a></li>
          <li><a href="#">Groupe</a></li>
          <li><a href="#">Projects</a></li>
          <li><a href="chat.php">Chat</a></li>
          <li><a href="userlist.php">Utilisateur</a></li>
          <li>
            <a href="#">
              <form method="post" enctype="multipart/form-data" action="logout.php">
                <button type="submit" class="bouton-logout" name="logout">Se déconnecter</button>
              </form>
            </a>
          </li>
        </ul>
      </div>
    </nav>
  </div>

  <!-- ... Existing code ... -->

  <div class="container">
    <h1>Edit Promo</h1>

    <!-- Display promo details form -->
    <form method="POST" action="update_promo.php">
      <!-- Display input fields for promo details -->
      <input type="hidden" name="promo_id" value="<?php echo $promo_id; ?>">
      <label for="name">Name:</label>
      <input type="text" id="name" name="name" value="<?php echo $promo['name'] ?? ''; ?>" required>

      <label for="start_date">Start Date:</label>
      <input type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d', strtotime($promo['start_date'] ?? '')); ?>" required>

      <label for="end_date">End Date:</label>
      <input type="date" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime($promo['end_date'] ?? '')); ?>" required>

      <label for="pilot">Pilot:</label>
      <select id="pilot" name="pilot" required>
        <?php foreach ($pilots as $pilot) : ?>
          <option value="<?php echo $pilot['id']; ?>" <?php echo ($promo['pilote_user_id'] ?? 0) == $pilot['id'] ? 'selected' : ''; ?>>
            <?php echo $pilot['first_name'] . ' ' . $pilot['last_name']; ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Save and Discard buttons -->
      <div class="save-discard-buttons">
        <button type="submit" class="save-btn">Save</button>
        <a href="edit_promo.php?id=<?php echo $promo_id; ?>" class="discard-link">Discard</a>
      </div>
    </form>

    <!-- Display list of users associated with the promo -->
    <h2>Users Associated with the Promo</h2>
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
              <form method="post" action="remove_user_from_promo.php">
                <input type="hidden" name="promo_id" value="<?php echo $promo_id; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <button type="submit" class="remove-user-btn">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Display list of users not associated with the promo -->
    <h2>Users Not Associated with the Promo</h2>
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
        var promoId = <?php echo $promo_id; ?>; // Retrieve the promo ID from PHP variable

        // Create a form element dynamically
        var form = document.createElement("form");
        form.action = "add_user_to_promo.php";
        form.method = "POST";

        // Create input fields for promo ID and user ID
        var promoIdField = document.createElement("input");
        promoIdField.type = "hidden";
        promoIdField.name = "promo_id";
        promoIdField.value = promoId;
        form.appendChild(promoIdField);

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
