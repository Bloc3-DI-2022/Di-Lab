<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_connection.php';

// Redirect to login if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}
    // Récupération des paramètres GET pour l'ID de l'utilisateur et l'ID de la promo
    $user_id = $_GET['user_id'];
    $promo_id = $_GET['promo_id'];

    // Préparation de la requête SQL pour supprimer la relation entre l'utilisateur et la promo
    $sql = "DELETE FROM promo_user WHERE id_user = ? AND id_promo = ?";

    // Préparer la déclaration
    if ($stmt = $conn->prepare($sql)) {
        
        // Lier les variables à la déclaration
        $stmt->bind_param('ii', $user_id, $promo_id);

        // Exécuter la déclaration
        $stmt->execute();

        // Fermer la déclaration
        $stmt->close();
    }
    
   

    // Rediriger l'utilisateur vers une page appropriée après la suppression
    header("Location: edit_user.php?id=$user_id");
    exit;

     // Fermer la connexion
     $conn->close();
?>
