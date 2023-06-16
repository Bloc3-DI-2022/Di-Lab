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
// FIN PHP 
?><!DOCTYPE html>

<head>
<title>Chat DiLAB</title>
<meta charset="utf-8">
<link rel="stylesheet" media="screen" href="promo.css" />
</head>

<body>

 <!-- Gestion screen -->
<div class="flex flex-col h-screen justify-between">
<!-- Bloc Navigation -->
<?php get_include("header.php"); ?> 


<!-- Bloc -->
<div class="container md:container md:mx-auto">
<!-- Table et recherche -->
  <div  iv class="table-plus-recherche">

 
    <div class="recherche-table flex flex-col ">
      <div class="flex flex-col justify-items-center justify-center md:div md:mx-auto pb-4">
       <h1 class="pb-4 text-2xl">PROMOS</h1>
       <h2>Filtres</h2>
       </div>

       <div class="flex justify-items-center justify-between md:div md:mx-auto">
      <div class="recherche-inputs">
        
      <div class="mb-3">
    <div class="relative mb-4 flex w-full flex-wrap items-stretch">
      <input
        type="text" id="search-user" placeholder="Search User"
        class="relative m-0 -mr-0.5 block w-[1px] min-w-0 flex-auto rounded-l border border-solid border-neutral-300 bg-transparent bg-clip-padding px-3 py-[0.25rem] text-base font-normal leading-[1.6] text-neutral-700 outline-none transition duration-200 ease-in-out focus:z-[3] focus:border-primary focus:text-neutral-700 focus:shadow-[inset_0_0_0_1px_rgb(59,113,202)] focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:placeholder:text-neutral-200 dark:focus:border-primary"
        
        aria-label="Search"
        aria-describedby="button-addon3" />

      <!--Search button-->
      <button
        class="relative z-[2] rounded-r border-2 border-primary px-6 py-2 text-xs font-medium uppercase text-primary transition duration-150 ease-in-out hover:bg-black hover:bg-opacity-5 focus:outline-none focus:ring-0"
        type="button"
        id="button-addon3"
        data-te-ripple-init>
        Search
      </button>
    </div>
</div>
      <input type="date" id="filter-start-date" placeholder="Filter By Start Date" class="form-control" style="max-width:120px;">
      <input type="date" id="filter-end-date" placeholder="Filter By End Date" class="form-control" style="max-width:120px;">
      <select id="filter-pilot" class="form-control" style="max-width:120px;">
      <option value="">All Pilots</option>
      <?php foreach ($pilots as $pilot) : ?>
        <option value="<?php echo $pilot['id']; ?>"><?php echo $pilot['first_name'] . ' ' . $pilot['last_name']; ?></option>
      <?php endforeach; ?>
      </select>
      </div>




  
       </div>









      
    </div>
  </div>
  <div class="flex flex-col mt-8">
    <div class="py-2 -my-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
      <div class="inline-block min-w-full overflow-hidden align-middle border-b border-gray-200 shadow sm:rounded-lg">
<!-- Début table -->
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
        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-10 h-10">
            <img class="w-10 h-10 rounded-full" src="https://source.unsplash.com/user/erondu"
            alt="admin dashboard ui">
          </div>

          <div class="ml-4">
          <div class="text-sm font-medium leading-5 text-gray-900">
          <?php echo $promotion['name']; ?>
          </div>
          </div>
        </div>
        </td>

        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
        <div class="text-sm leading-5 text-gray-500">
          <?php echo $promotion['start_date']; ?>
        </div>
        </td>

        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
        <span
        class="inline-flex px-2 text-xs font-semibold leading-5 text-grey-600 bg-amber-300 rounded-full">
        <?php echo $promotion['end_date']; ?></span>
        </td>

        <td data-pilot-id="<?php echo $promotion['pilote_user_id']; ?>"
        class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
        <?php echo $promotion['first_name'] . ' ' . $promotion['last_name']; ?>
        </td>
        <td
        class="px-6 py-4 text-sm leading-5 text-gray-500 whitespace-no-wrap border-b border-gray-200">
        <a href="edit_promo.php?id=<?php echo $promotion['id']; ?>"> <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-300" fill="none"
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
    <div class="create-promo-button flex justify-center md:div md:mx-auto flex pt-3 pb-3">
      <form method="post" action="create_promo.php" class="flex  justify-center justify-items-center md:form md:mx-auto flex">
        <p class="mr-4">Créer une promo</p>
        <button type="submit" class="create-promo-btn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>
</button> 
      </form>
    </div>

      </div>
    </div>
  </div> 
</div> 
<!-- Fin tableau + recherche  -->




<!-- Ajout footer -->
  <?php include("footer.php"); ?>
  
</div>
<!-- Scripts -->
  <script src="TableFilter.min.js" defer></script>
  <script src="TableFilter.js" defer></script>
  <script>
    import {
  Ripple,
  initTE,
} from "tw-elements";

initTE({ Ripple });
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

    // Add event listeners to filter inputs
    document.getElementById("filter-name").addEventListener("input", function() {
      var filterText = this.value.toLowerCase();
      var rows = document.querySelectorAll("#filter tbody tr");

      rows.forEach(function(row) {
        var promoName = row.querySelector("td:nth-child(1)").innerText.toLowerCase();
        var showRow = promoName.includes(filterText);
        row.style.display = showRow ? "table-row" : "none";
      });
    });

    document.getElementById("filter-start-date").addEventListener("input", function() {
      var filterStartDate = this.value;
      var rows = document.querySelectorAll("#filter tbody tr");

      rows.forEach(function(row) {
        var promoStartDate = row.querySelector("td:nth-child(2)").innerText;
        var showRow = promoStartDate >= filterStartDate || filterStartDate === "";
        row.style.display = showRow ? "table-row" : "none";
      });
    });

    document.getElementById("filter-end-date").addEventListener("input", function() {
      var filterEndDate = this.value;
      var rows = document.querySelectorAll("#filter tbody tr");

      rows.forEach(function(row) {
        var promoEndDate = row.querySelector("td:nth-child(3)").innerText;
        var showRow = promoEndDate <= filterEndDate || filterEndDate === "";
        row.style.display = showRow ? "table-row" : "none";
      });
    });

    document.getElementById("filter-pilot").addEventListener("change", function() {
      var filterPilotId = this.value;
      var rows = document.querySelectorAll("#filter tbody tr");

      rows.forEach(function(row) {
        var promoPilotId = row.querySelector("td:nth-child(4)").getAttribute("data-pilot-id");
        var showRow = filterPilotId === "" || promoPilotId === filterPilotId;
        row.style.display = showRow ? "table-row" : "none";
      });
    });
  </script>

</body>
</html>