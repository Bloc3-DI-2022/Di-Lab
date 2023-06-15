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
include("fonction.php");
?><html>
    <head>
        <title>Edit User</title>
        <link rel='stylesheet' type='text/css' href='edit_user.css'/>
        <meta charset="UTF-8">
    </head>

    <body>

    <div class="flex flex-col h-screen justify-between">
<!-- Bloc Navigation -->
<?php get_include("header.php"); ?> 



<div class="container-chat container md:container md:mx-auto flex h-4/5">


<div class="w-full md:w-1/2 py-10 px-5 md:px-10 self-center">
                <div class="text-center mb-10">
                    <h1 class="font-bold text-3xl text-gray-900">Modifications</h1>
                    <p>Choississez l'information à modifier</p>
                </div>
                <div>
                <form action='update_user.php' method='post'>
                <input type='hidden' name='id' value='<?= $user['id'] ?>'/>
                    <div class="flex -mx-3">
                        <div class="w-1/2 px-3 mb-5">
                            <label for="first_name" class="text-xs font-semibold px-1">Prénom</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-account-outline text-gray-400 text-lg"></i></div>
                                <input type="text" class="w-full -ml-10 pl-10 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-indigo-500" name='first_name' value='<?= $user['first_name'] ?>'>
                            </div>
                        </div>
                        <div class="w-1/2 px-3 mb-5">
                            <label for="last_name" class="text-xs font-semibold px-1">Nom</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-account-outline text-gray-400 text-lg"></i></div>
                                <input type="text" class="w-full -ml-10 pl-10 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-indigo-500" name='last_name' value='<?= $user['last_name'] ?>'>
                            </div>
                        </div>
                    </div>
                    <div class="flex -mx-3">
                        <div class="w-full px-3 mb-5">
                            <label for="email" class="text-xs font-semibold px-1">Email</label>
                            <div class="flex">
                                <div class="w-10 z-10 pl-1 text-center pointer-events-none flex items-center justify-center"><i class="mdi mdi-email-outline text-gray-400 text-lg"></i></div>
                                <input type="email" class="w-full -ml-10 pl-10 pr-3 py-2 rounded-lg border-2 border-gray-200 outline-none focus:border-indigo-500" name='email' value='<?= $user['email'] ?>'>
                            </div>
                        </div>
                    </div>
                    <div class="flex -mx-3">
                        <div class="w-full px-3 mb-12">
                            <label for="user_type_id" class="text-xs font-semibold px-1">Type utilisateur </label>
                            <select name='user_type_id'>
            <?php foreach ($userTypes as $userType): ?>
                <option value='<?= $userType['id'] ?>' <?= $userType['id'] == $user['user_type_id'] ? "selected" : "" ?>><?= $userType['type'] ?></option>
            <?php endforeach; ?>
        </select><br>
                        </div>
                        </div>
                    <div class="flex -mx-3">
                        <div class="w-full px-3 mb-12">
                            <label for="promo_id" class="text-xs font-semibold px-1">Ajouter une promo </label>
                            <select name='promo_id'>
            <?php foreach ($promos as $promo): ?>
                <option value='<?= $promo['id'] ?>'><?= $promo['name'] ?></option>
            <?php endforeach; ?>
        </select><br>
                        </div>


                        
                    </div>
                    <div class="flex -mx-3">
                        <div class="w-full px-3 mb-5">
                            <button class="block w-full max-w-xs mx-auto bg-indigo-500 hover:bg-indigo-700 focus:bg-indigo-700 text-white rounded-lg px-3 py-3 font-semibold">Enregistrer les modifications</button>
                        </div>
                    </div>
                </form>
                </div>
            </div>

    

            <div class="w-full flex flex-col items-center justify-center md:w-1/2 py-10 px-5 md:px-10">
            <button type="submit" name="logout" class="p-2 lg:px-4 md:mx-2 text-indigo-600 text-center border border-solid border-indigo-600 rounded hover:bg-indigo-600 hover:text-white transition-colors duration-300 mt-1 md:mt-0 md:ml-1 mb-12"><a href="userlist.php">Retour à la page précedente</a></button>
                         <!--  -->
        <table class="overflow-auto h-3/5 border-2 border-gray-200 place-items-center" id="all-users-table">
<!-- Nom colonnes -->
      <thead>
        <tr>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Nom promotion</th>
        <th
        class="px-6 py-3 text-xs font-medium leading-4 tracking-wider text-left text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
        Supprimer</th>
        
        
        </tr>
      </thead>

<!-- lignes colonnes -->
      <tbody class="bg-white">
      <?php foreach ($userPromos as $promo): ?>
        <tr>
        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
         

          <div class="ml-4">
          <div class="text-sm font-medium leading-5 text-gray-900">
          <?php echo $promo['name']; ?>
          </div>
          </div>
        </div>
        </td>

        <td class="px-6 py-1 whitespace-no-wrap border-b border-gray-200">
        <div class="text-sm leading-5 text-gray-500">
        <form method="GET" action="remove_promo_from_user.php">
    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
    <input type="hidden" name="promo_id" value="<?= $promo['id']; ?>">
    <button type="submit">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
        </svg>
    </button>
</form>
        
        </div>
        </td>
        
       
        </tr>
        
        <?php endforeach; ?>

      </tbody>
    </table>
</div>



      
    </form>
    </div>

   



<?php include("footer.php"); ?>
</div>
    </body>
    </html>