<?php
require 'db_connection.php';

$sql = "SELECT user.*, userTypes.type FROM user INNER JOIN userTypes ON user.user_type_id = userTypes.id";
$result = $conn->query($sql);


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
$sql_all_users = "SELECT id, first_name, last_name, email, is_admin, user_type_id   FROM user WHERE id NOT IN (SELECT id_user FROM promo_user WHERE 999 = $promo_id)";
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
?>
<html>
    
    <head>
        <title>Users List</title>
        <meta charset="UTF-8">
        <link rel='stylesheet' type='text/css' href=''/>
    </head>


    <body>
    
    <div class="flex flex-col h-full md:h-full justify-between">

    <?php get_include("header.php"); 
    ?> 

  



<div class="container md:container md:mx-auto">
    <div class="flex flex-col mt-8 justify-items-center justify-center md:div md:mx-auto pb-4">
    <div class="">
      <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg">
<!-- Début table -->
<div class="flex flex-col mt-8 items-center justify-center md:div md:mx-auto pb-4">
<h1 class="font-semibold text-3xl mb-20">Liste des utilisateurs</h1> 


<div class="recherche-inputs">
      <input type="text" id="search-user" class="w-96 -ml-10 pl-10 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-indigo-500" placeholder="Recherche par Prénom ou Type utilisateur" />
    </div>
    </div>
      <table class="min-w-full" id="all-users-table">
<!-- Nom colonnes -->
      <thead>
        <tr>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
       Est admin </th>
       <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
       Type utilisateur</th>
        
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Prénom</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Nom</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Email</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Modifier</th>
        
        </tr>
      </thead>

<!-- lignes colonnes -->
      <tbody class="bg-white">
      <?php foreach ($all_users as $user) : ?>
        <tr>
           
        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200 <?= $user['is_admin'] == 1 ? "is_admin" : "" ?>">
         

          <div class="ml-4">
          <div class="text-sm font-medium leading-5 text-gray-900">
          <?php if ($user['is_admin'] == 1): ?>
          
          <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'
                                 fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round'
                                 stroke-linejoin='round' class='feather feather-check'>
                                <polyline points='20 6 9 17 4 12'></polyline>
                            </svg>
                        <?php endif; ?>
          </div>
          </div>
        </div>
        </td>
        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
         

         <div class="ml-4">
         <div class="text-sm font-medium leading-5 text-gray-900">
         <?php echo $user['user_type_id']; ?>
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

        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
         

         <div class="ml-4">
         <div class="text-sm font-medium leading-5 text-gray-900">
         <?php echo $user['email']; ?>
         </div>
         </div>
       </div>
       </td>

        
        <td
        class="px-6 py-1 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
        <button class="add-user-btn" data-user-id="<?php echo $user['id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-400" fill="none"
        viewBox="0 0 24 24" stroke="currentColor" onclick="location.href='edit_user.php?id=<?= $user['id'] ?>'"> 
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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
<?php get_include("footer.php"); 

    ?> 

    </div>
    <script> // Add event listener to search input
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
    });</script>
    </body>
    </html>