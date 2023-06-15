<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//error displaying

require 'db_connection.php';
$user_id = $_SESSION['id'];
$user_type_id = 0; 

$sql = "SELECT user_type_id FROM user WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    // Lier l'ID utilisateur à la déclaration
    $stmt->bind_param('i', $user_id);
    
    // Exécuter la déclaration
    if ($stmt->execute()) {
        // Stocker le résultat
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            // Lier le résultat à la variable $user_type_id
            $stmt->bind_result($user_type_id);

            // Récupérer le résultat
            $stmt->fetch();

            // Vous pouvez maintenant utiliser $user_type_id comme vous le souhaitez
        } else {
            // Pas d'utilisateur avec cet ID, gérer cette situation
        }
    } else {
        // Une erreur est survenue lors de l'exécution de la déclaration
    }

    // Fermer la déclaration
    $stmt->close();
  
  }
?>
<script src="https://cdn.tailwindcss.com"></script>
 

 <!-- Bloc Navigation -->


  

  <div class="header-2 ">

  <nav class="bg-white pt-8
   pb-8">
    <div class="container px-4 mx-auto md:flex md:items-center">

      <div class="flex justify-between items-center">
        <a href="accueil.php" class="font-bold text-xl text-indigo-600">DiLAB</a>
        
        <button class="border border-solid border-gray-600 px-3 py-1 rounded text-gray-600 opacity-50 hover:opacity-75 md:hidden" id="navbar-toggle">
          <i class="fas fa-bars"></i>
        </button>
      </div>
      
      <div class="hidden md:flex flex-col md:flex-row md:ml-auto mt-3 md:mt-0 h-10" id="navbar-collapse">
    <a href="accueil.php" class="p-2 lg:px-4 md:mx-2 text-white rounded bg-indigo-600">Accueil</a>
    <?php if ($_SESSION['id']): ?>
        <?php if ($user_type_id === 1 || $user_type_id === 3 ): ?>
          <a href="promo.php" class="p-2 lg:px-4 md:mx-2 text-gray-600 rounded hover:bg-gray-200 hover:text-gray-700 transition-colors duration-300">Promo</a>
            <?php if ($user_type_id === 1): ?>
              <a href="userlist.php" class="p-2 lg:px-4 md:mx-2 text-indigo-600 text-center border border-transparent rounded hover:bg-indigo-100 hover:text-indigo-700 transition-colors duration-300">Utilisateur</a>
                <?php endif; ?>
        <?php endif; ?>
    <a href="group.php" class="p-2 lg:px-4 md:mx-2 text-gray-600 rounded hover:bg-gray-200 hover:text-gray-700 transition-colors duration-300">Groupe</a>
    <a href="project.php" class="p-2 lg:px-4 md:mx-2 text-gray-600 rounded hover:bg-gray-200 hover:text-gray-700 transition-colors duration-300">Projets</a>
    <a href="chat.php?conversation_id=0" class="p-2 lg:px-4 md:mx-2 text-gray-600 rounded hover:bg-gray-200 hover:text-gray-700 transition-colors duration-300">Chat</a>
    <form action="logout.php" method="post">
        <button type="submit" name="logout" class="p-2 lg:px-4 md:mx-2 text-indigo-600 text-center border border-solid border-indigo-600 rounded hover:bg-indigo-600 hover:text-white transition-colors duration-300 mt-1 md:mt-0 md:ml-1">Se déconnecter</button>
    </form>
    <?php endif; ?>
</div>

    </div>
  </nav>
  </div>

