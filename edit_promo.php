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
$user_id = $_SESSION['id'];

// Retrieve the promo details from the database based on the promo ID
$sql_promo = "SELECT * FROM promo WHERE id = $promo_id";
$result_promo = mysqli_query($conn, $sql_promo);

if ($result_promo && mysqli_num_rows($result_promo) > 0) {
  $promo = mysqli_fetch_assoc($result_promo);
} else {
  // Handle the case when the promo is not found
  // For example, display an error message or redirect to an error page
}
// Handle form submission for admin message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $promo_id = $_POST['promo_id'];
  $pilot_id = $_POST['pilot_id'];
  $message = $_POST['message'];
  $priority = $_POST['priority'];

  $sql = "INSERT INTO admin_chat (id_promo, id_pilot, message, date, priority) VALUES (?, ?, ?, NOW(), ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "iisi", $promo_id, $pilot_id, $message, $priority);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
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
<title>Edition promotion</title>
<meta charset="utf-8">
<link rel="stylesheet" media="screen" href="" />

<body>
  <!-- Gestion screen -->
  <div class="flex flex-col h-full md:h-full justify-between">
<!-- Bloc Navigation -->
<?php get_include("header.php"); ?> 

  
  
  
  <div class="container md:container md:mx-auto flex h-4/5 ">

  

    <div class="w-full md:w-1/2 py-10 px-5 md:px-10 self-center ">
                <div class="text-center mb-10">
                    <h1 class="font-bold text-3xl text-gray-900">Edition promotion</h1>
                    <p>Choississez l'information à modifier</p>
                </div>
                <div>
                <form method="POST" action="update_promo.php">
                <input type="hidden" name="promo_id" value="<?php echo $promo_id; ?>">
                
                <div class="flex -mx-3">
                        <div class="w-full px-3 mb-5">
                        <label for="name" class="text-xs font-semibold px-1">Nom de la promotion</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-email-outline text-gray-400 text-lg"></i></div>
                                <input type="text" class="w-full -ml-10 pl-10 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-indigo-500" type="text" id="name" name="name" value="<?php echo $promo['name'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="flex -mx-3">
                        
                    </div>
                    <div class="flex -mx-3">
                        <div class="w-1/2 px-3 mb-5">
                        <label for="end-date" class="text-xs font-semibold px-1">Fin promotion</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-account-outline text-gray-400 text-lg"></i></div>
                                <input class="w-full -ml-10 pl-10 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-indigo-500" type="date" id="end_date" name="end_date" value="<?php echo date('Y-m-d', strtotime($promo['end_date'] ?? '')); ?>" required>
                               </div>
                        </div>

                        <div class="w-1/2 px-3 mb-5">
                            <label for="start-date" class="text-xs font-semibold px-1">Début promotion</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-account-outline text-gray-400 text-lg"></i></div>
                                <input type="date" class="w-full -ml-10 pl-10 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-indigo-500" type="date" id="start_date" name="start_date" value="<?php echo date('Y-m-d', strtotime($promo['start_date'] ?? '')); ?>" required>
                            </div>
                        </div>
                    </div>
                   
                    <div class="flex -mx-3">
                        <div class="w-full px-3 mb-12">
                            <label for="pilot" class="text-xs font-semibold px-1">Pilote</label>
                            <select id="pilot" name="pilot" required>
        <?php foreach ($pilots as $pilot) : ?>
          <option value="<?php echo $pilot['id']; ?>" <?php echo ($promo['pilote_user_id'] ?? 0) == $pilot['id'] ? 'selected' : ''; ?>>
            <?php echo $pilot['first_name'] . ' ' . $pilot['last_name']; ?>
          </option>
        <?php endforeach; ?>
      </select><br>
                        </div>
                        </div>
                        <div class="save-discard-buttons flex items-center">
        <button type="submit" class="save-btn block w-1/3 max-w-xs mx-auto bg-indigo-500 hover:bg-indigo-700 focus:bg-indigo-700 text-white rounded-lg px-3 py-3 font-semibold">Save</button>
        <a href="edit_promo.php?id=<?php echo $promo_id; ?>" class="discard-link block w-1/3 max-w-xs mx-auto bg-indigo-500 hover:bg-indigo-700 focus:bg-indigo-700 text-white rounded-lg px-3 py-3 font-semibold">Discard</a>
      </div>
                    
                </form>
                </div>
                <h1 class="font-bold text-3xl text-gray-900 text-center ">Message important de promotion</h1>
                <div class="w-full px-5 flex flex-col ">
             
      <form method="post" class="form-chat" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <input type="hidden" id="promo_id" name="promo_id" value="<?php echo $promo_id?>">
          <input type="hidden" id="pilot_id" name="pilot_id" value="<?php echo $user_id?>">

          <div class="flex flex-col justify-evenly mb-4">
              <label for="message" class="font-bold text-gray-700">Message:</label>
              <textarea name="message" required rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>

              <label for="priority" class="mt-4 font-bold text-gray-700">Priority:</label>
              <select id="priority" name="priority" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
              </select>
          </div>
        
          <input type="submit" value="Submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 border border-blue-700 rounded">
      </form>
  </div>
            </div>
           
        

<div class="w-full flex flex-col items-center justify-center md:w-1/2 py-10 px-5 md:px-10 space-y-10">
   <div class="tableaux flex md:div md:mx-auto w-5/6 ">
 <div class="tableaux-sans-liste-user ">
    <div class="flex flex-col mt-8">
    <div class="py-1 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
      <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg flex flex-col items-center space-y-6">
<!-- Début table -->
<h1 class="font-bold text-3xl text-gray-900">Utilisateurs de la promo</h1>
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

    
   

    
    
</div>
</div>

  <div class="tableaux flex md:div md:mx-auto w-5/6">
    <div class="py-2 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
      <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg flex flex-col items-center space-y-4">
<!-- Début table -->
<h1 class="font-bold text-3xl text-gray-900">Liste des utilisateurs</h1> 
<div class="recherche-inputs">
      <input type="text" id="search-user" placeholder="Search User" />
    </div>
      <table class="min-w-full overflow-auto" id="all-users-table">
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
