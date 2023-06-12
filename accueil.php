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
  <div class="nav">
    <?php include("header.php"); ?> 
  </div>
</body>
</html>