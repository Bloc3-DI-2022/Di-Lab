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
include("fonction.php");
?><html>
<head>
    <title>Edit Project</title>
    <meta charset="utf-8">
    <link rel="stylesheet" media="screen" href="edit_project.css">
</head>
<body>


<div class="flex flex-col h-full md:h-full justify-between">
<!-- Bloc Navigation -->
<?php get_include("header.php"); ?> 

  
  
  
  <div class="container md:container md:mx-auto flex h-4/5 ">

  

    <div class="w-full md:w-1/2 py-10 px-5 md:px-10 self-center ">
    
        <h1 class="font-bold text-3xl text-gray-900 mb-20 content-center">Edit Project</h1>


        
        <div class="w-full md:w-2/3 py-10 px-5 md:px-10 self-center ">
<div> 
        <div class="text-center mb-10">
                    <h1 class="font-bold text-3xl text-gray-900">Modifications</h1>
                    <p>Choississez l'information à modifier</p>
                </div>
                <div>
                <form action='update_project.php' method='post'>
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                    <div class="flex -mx-3">
                        <div class="w-1/2 px-3 mb-5">
                            <label for="first_name" class="text-xs font-semibold px-1">Partagé avec l'intervenant</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-account-outline text-gray-400 text-lg"></i></div>
                                <input class="accent-amber-300" type="checkbox" id="share-with-intervenant" name="share_with_intervenant" value="1" <?php echo ($project['share_with_intervenant'] ?? '') ? 'checked' : ''; ?>>  </div>
                        </div>
                        <div class="w-1/2 px-3 mb-5">
                            <label for="last_name" class="text-xs font-semibold px-1">Partagé avec le pilote</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-account-outline text-gray-400 text-lg"></i></div>
                                <input class="accent-amber-300" type="checkbox" id="share-with-pilot" name="share_with_pilot" value="1" <?php echo ($project['share_with_pilot'] ?? '') ? 'checked' : ''; ?>>       </div>
                        </div>
                    </div>
                    <div class="flex -mx-3">
                        <div class="w-full px-3 mb-5">
                            <label for="email" class="text-xs font-semibold px-1">Nom du projet</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-email-outline text-gray-400 text-lg"></i></div>
                                <input  type="text" id="name" name="name" value="<?php echo $project['name'] ?? ''; ?>" required>   
        </div>
                        </div>

                    </div>
                   
                    <!-- <div class="w-full px-3 mb-5">
                            <label for="email" class="text-xs font-semibold px-1">Partagé avec l'intervenant</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-email-outline text-gray-400 text-lg"></i></div>
                                <input type="checkbox" id="share-with-intervenant" name="share_with_intervenant" value="1" <?php echo ($project['share_with_intervenant'] ?? '') ? 'checked' : ''; ?>>
        </div>
                        </div> -->
                  
                    <div class="save-discard-buttons flex items-center">
        <button type="submit" class="save-btn block w-1/3 max-w-xs mx-auto bg-amber-300 hover:bg-amber-400 focus:bg-indigo-700 text-black rounded-lg px-3 py-3 font-semibold">Save</button>
        <a href="edit_promo.php?id=<?php echo $project_id; ?>" class="discard-link block w-1/3 max-w-xs mx-auto bg-amber-300 hover:bg-amber-400 focus:bg-indigo-700 text-black rounded-lg px-3 py-3 font-semibold">Discard</a>
      </div>
                </form>
                </div>
</div>
            </div>
    




               
            </div>
           
        

<div class="w-full flex flex-col items-center justify-center md:w-1/2 py-10 px-5 md:px-10 space-y-10">


<!--  -->
 <!-- Display list of users associated with the project -->

<!--  -->
   <div class="tableaux flex h-1/2  md:div md:mx-auto w-5/6">
 <div class="tableaux-sans-liste-user ">
    <div class="flex flex-col mt-8">
    <div class="py-1 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
      <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg flex flex-col items-center space-y-6">
<!-- Début table -->
<h1 class="font-bold text-3xl text-gray-900">Utilisateurs du projet</h1>
<?php if ($users) : ?>
    <div class=" overflow-x-hidden overflow-y-auto h-56">
      <table class="min-w-full ">
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
       Action</th>
       
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
        <div class="text-sm leading-5 text-gray-500">
          <?php echo $user['last_name']; ?>
        </div>
        </td>
        

        
        <td
        class="px-6 py-1 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
        <form method="post" action="remove_user_from_project.php">
            
                <input type="hidden" name="promo_id" value="<?php echo $project_id; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <button type="submit" class="remove-user-btn">Remove</button>


              </form>
        </td>
        </tr>

        <?php endforeach; ?>

      </tbody>
    </table>
    </div>
    <?php else : ?>
            <p>No users associated with the project.</p>
        <?php endif; ?>
    <!-- Fin tableau -->
    </div></div>
  </div>

    
   

    
    
</div>
</div>



  <div class="tableaux flex h-1/2  md:div md:mx-auto w-5/6">
    <div class="py-2 -my-2  sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
      <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg flex flex-col items-center space-y-4">
<!-- Début table -->
<h1 class="font-bold text-3xl text-gray-900">Liste des utilisateurs non associés</h1> 
<?php if ($not_associated_users) : ?>
<div class="recherche-inputs">
      <input type="text" id="search-user" placeholder="Search User" />
    </div>
    <div class=" overflow-x-hidden  h-56">
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
        Ajouter</th>
        
        </tr>
      </thead>

<!-- lignes colonnes -->
      <tbody class="bg-white">
      <?php foreach ($not_associated_users as $user) : ?>
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
        <button class="add-user-btn " data-user-id="<?php echo $user['id']; ?>"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg></button>
        </td>
        </tr>

        <?php endforeach; ?>

      </tbody>
    </table>
      </div>
    <?php else : ?>
            <p>All users are associated with the project.</p>
        <?php endif; ?>
    <!-- Fin tableau -->

    </div>
    

  </div>
  </div>

  </div>
      </div>
  <!-- Ajout footer -->





<!-- FIN NOUVEAU CODE -->




<?php include("footer.php"); ?>
</div>

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
