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

// Fetch promotions with pilot names from the database
$sql_promotions = "SELECT promo.id, promo.name, promo.start_date, promo.end_date, user.first_name, user.last_name, promo.pilote_user_id
                   FROM promo
                   INNER JOIN user ON promo.pilote_user_id = user.id";
$result_promotions = mysqli_query($conn, $sql_promotions);
$promotions = mysqli_fetch_all($result_promotions, MYSQLI_ASSOC);
mysqli_free_result($result_promotions);

mysqli_close($conn);
include("fonction.php");
?><html>
<title>Chat DiLAB</title>
<meta charset="utf-8">
<link rel="stylesheet" media="screen" href="" />

<body>
  <!-- Gestion screen -->
<div class="flex flex-col h-screen justify-between">
<!-- Bloc Navigation -->
<?php get_include("header.php"); ?> 

  
  
  
  <div class="container flex flex-col md:container md:mx-auto">

  <div class="md:div md:mx-auto">
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
    </div>
 


   <div class="tableaux flex md:div md:mx-auto grow">
 <div class="tableaux-sans-liste-user ">
    <div class="flex flex-col mt-8">
    <div class="py-1 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
      <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg">
<!-- Début table -->
<h1>Utilisateurs de la promo</h1>
      <table class="min-w-full">
<!-- Nom colonnes -->
      <thead>
        <tr>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        ID utilisateur</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Prénom</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
       Nom</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
       Supprimer</th>
       
        </tr>
      </thead>

<!-- lignes colonnes -->
      <tbody class="bg-white">
        <?php foreach ($users as $user) : ?>
        <tr>
        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
         

          <div class="ml-1">
          <div class="text-sm font-medium leading-5 text-gray-900">
          <?php echo $user['id']; ?>
          </div>
          </div>
        </div>
        </td>

        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
        <div class="text-sm leading-5 text-gray-500">
          <?php echo $user['first_name']; ?>
        </div>
        </td>

        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
        <span
        class="text-sm leading-5 text-gray-500">
        <?php echo $user['last_name']; ?></span>
        </td>

        
        <td
        class="px-6 py-1 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
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
    <!-- Fin tableau -->
    </div></div>
  </div>

    
   

    
    <div class="flex flex-col mt-8">
    <div class="py-2 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
      <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg">
<!-- Début table -->
<h1>Choix de la promo</h1>
      <table class="min-w-full">
<!-- Nom colonnes -->
      <thead>
        <tr>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Promotion</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Date de début</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Date de fin</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Pilote</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Modifier</th>
        </tr>
      </thead>

<!-- lignes colonnes -->
      <tbody class="bg-white">
        <?php foreach ($promotions as $promotion) : ?>
        <tr>
        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
         

          <div class="ml-4">
          <div class="text-sm font-medium leading-5 text-gray-900">
          <?php echo $promotion['name']; ?>
          </div>
          </div>
        </div>
        </td>

        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
        <div class="text-sm leading-5 text-gray-500">
          <?php echo $promotion['start_date']; ?>
        </div>
        </td>

        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
        <span
        class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">
        <?php echo $promotion['end_date']; ?></span>
        </td>

        <td data-pilot-id="<?php echo $promotion['pilote_user_id']; ?>"
        class="px-6 py-1 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
        <?php echo $promotion['first_name'] . ' ' . $promotion['last_name']; ?>
        </td>
        <td
        class="px-6 py-1 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
        <a href="edit_promo.php?id=<?php echo $promotion['id']; ?>"> <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-400" fill="none"
        viewBox="0 0 24 24" stroke="currentColor"> 
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg></a>
        </td>
        </tr>

        <?php endforeach; ?>

      </tbody>
    </table>
    <!-- Fin tableau -->
    <!-- Bouton création promo -->
   

      </div>
    </div>
</div>
</div>

  <div class="flex flex-col mt-8">
    <div class="py-2 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
      <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg">
<!-- Début table -->
<h1>Liste des utilisateurs</h1> 
<div class="recherche-inputs">
      <input type="text" id="search-user" placeholder="Search User" />
    </div>
      <table class="min-w-full" id="all-users-table">
<!-- Nom colonnes -->
      <thead>
        <tr>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        ID utilisateur</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Prénom</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Nom</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Ajouter à la promo</th>
        
        </tr>
      </thead>

<!-- lignes colonnes -->
      <tbody class="bg-white">
      <?php foreach ($all_users as $user) : ?>
        <tr>
        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
         

          <div class="ml-4">
          <div class="text-sm font-medium leading-5 text-gray-900">
          <?php echo $user['id']; ?>
          </div>
          </div>
        </div>
        </td>

        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
        <div class="text-sm leading-5 text-gray-500">
          <?php echo $user['first_name'] ?>
        </div>
        </td>

        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
        <span
        class="text-sm leading-5 text-gray-500">
        <?php echo $user['last_name']; ?></span>
        </td>

        
        <td
        class="px-6 py-1 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
        <button class="add-user-btn" data-user-id="<?php echo $user['id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="blue" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg></button>
        </td>
        </tr>

        <?php endforeach; ?>

      </tbody>
    </table>
    <!-- Fin tableau -->

    </div>
    

  </div>
    </div>
    </div>
  
      </div>
  <!-- Ajout footer -->
  <?php include("footer.php"); ?>

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
