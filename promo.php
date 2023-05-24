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
<link rel="stylesheet" media="screen" href="promo.css"/>

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
        <li><a href="chat.php">Utilisateur</a></li>
        <li><a href="#"><form method="post" enctype="multipart/form-data" action="logout.php">
      <button type="submit" class="bouton-logout" name="logout">Se déconnecter</button>
      </form> </a></li>
        </ul>
    </div>

		</nav>
	</div>




  <div class="container">

<div class="table-plus-recherche">
    <h1>PROMOS</h1>
    <div class="recherche-table">
    <h2>Filtres</h2>
            <div class="recherche-inputs">
                
                <input type="text" id="filter0" placeholder="Filter By Name" class="form-control" style="max-width:120px;">
                <input type="text" id="filter1" placeholder="Filter By Email" class="form-control" style="max-width:120px;">
                <input type="text" id="filter2" placeholder="Filter By Status" class="form-control" style="max-width:120px;">
                <input type="text" id="filter3" placeholder="Filter By Status" class="form-control" style="max-width:120px;">
                <input type="text" id="filter4" placeholder="Filter By Status" class="form-control" style="max-width:120px;">

            </div>
            <div class="cadre-table-scroll">
            <table id="filter" class="table-promos">
                <thead>
                    <tr>
                        <th>Nom promotion</th>
                        <th>Champs 2</th>
                        <th>Champs 3</th>
                        <th>Champs 4</th>
                        <th>Champs 5</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>John Doe</td>
                        <td>john@gmail.com</td>
                        <td>active</td>
                        <td>Oui</td>
                        <td>Le macramé</td>
                    </tr>
                    <tr>
                        <td>Bill Doe</td>
                        <td>bill@gmail.com</td>
                        <td>active</td>
                        <td>Non</td>
                        <td>Moustique</td>
                    </tr>
                    <tr>
                        <td>Bill Doe</td>
                        <td>bill@gmail.com</td>
                        <td>active</td>
                        <td>Non</td>
                        <td>Moustique</td>
                    </tr>
                    <tr>
                        <td>Bill Doe</td>
                        <td>bill@gmail.com</td>
                        <td>active</td>
                        <td>Non</td>
                        <td>Moustique</td>
                    </tr>

                    <tr>
                        <td>Jane Doe</td>
                        <td>jane@yahoo.com</td>
                        <td>disabled</td>
                        <td>Oui</td>
                        <td>La pêche</td>
                    </tr>
                    <tr>
                        <td>Bill Doe</td>
                        <td>bill@gmail.com</td>
                        <td>active</td>
                        <td>Non</td>
                        <td>Moustique</td>
                    </tr>
                    <tr>
                        <td>Jill Doe</td>
                        <td>Jill@Yahoo.com</td>
                        <td>disabled</td>
                        <td>Oui</td>
                        <td>Cerf-volant</td>
                    </tr>
                    <tr>
                        <td>Jill Doe</td>
                        <td>Jill@Yahoo.com</td>
                        <td>disabled</td>
                        <td>Oui</td>
                        <td>Cerf-volant</td>
                    </tr>
                    <tr>
                        <td>Jill Doe</td>
                        <td>Jill@Yahoo.com</td>
                        <td>disabled</td>
                        <td>Oui</td>
                        <td>Cerf-volant</td>
                    </tr>
                    <tr>
                        <td>Jill Doe</td>
                        <td>Jill@Yahoo.com</td>
                        <td>disabled</td>
                        <td>Oui</td>
                        <td>Cerf-volant</td>
                        
                    </tr>
                    <tr>
                        <td>Jill Doe</td>
                        <td>Jill@Yahoo.com</td>
                        <td>disabled</td>
                        <td>Oui</td>
                        <td>Cerf-volant</td>
                    </tr>
                </tbody>
               
            </table>
            </div>
        </div>
    </div>
    </div>

  




    <script src="TableFilter.min.js" defer></script>
    <script src="TableFilter.js" defer></script>
  </body>
 

</html>