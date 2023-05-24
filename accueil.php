<?php 
session_start();



require_once 'db_connection.php';

//redirect to login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: index.php");
  exit;
}

mysqli_close($conn); 
?><!DOCTYPE html>
<html>
<title>Chat DiLAB</title>
<meta charset="utf-8">
<link rel="stylesheet" media="screen" href="accueil.css"/>

<body>
 <!-- Bloc Navigation -->

	<div class="container" id="bloc-nav">
		<nav class="barre-nav">
			
    <div class="navbar-header">
				<a class="renvoi-accueil" href="accueil.php">Di-Lab</a>
		</div>

    <div class="collapse navbar-collapse navbar-1">
      <ul class="site-navigation nav">
        <li><a href="promo.php">Promos</a></li>
        <li><a href="#">Groupe</a></li>
        <li><a href="#">Projects</a></li>
        <li><a href="chat.php">Chat</a></li>
        <li><a href="chat.php">Utilisateur</a></li>
        <li><a href="#"><form method="post" enctype="multipart/form-data" action="logout.php">
      <button type="submit" class="bouton-logout" name="logout">Se déconnecter</button>
      </form> </a></li>
        </ul>
    </div>

		</nav>
	</div>




  
  </body>
 

</html>