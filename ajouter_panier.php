<?php
//message d'erreur
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session 
session_start();

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Vérification que les champs existent
    if (isset($_POST['id_produit']) && isset($_POST['quantite'])) {

        // Récupération et conversion en entier
        $id_produit = (int) $_POST['id_produit'];
        $quantite = (int) $_POST['quantite'];

        // Vérification des valeurs
        if ($id_produit > 0 && $quantite > 0) {

            // Si le panier n'existe pas encore
            if (!isset($_SESSION['panier'])) {
                $_SESSION['panier'] = array();
            }

            // Si le produit est déjà dans le panier
            if (isset($_SESSION['panier'][$id_produit])) {
                $_SESSION['panier'][$id_produit] += $quantite;
            } else {
                $_SESSION['panier'][$id_produit] = $quantite;
            }

            // Redirection vers la page des produits
            header("Location: page-produit.html");
            exit();

        } else {
            echo "Erreur : ID produit ou quantité invalide.";
        }

    } else {
        echo "Erreur : formulaire non soumis.";
    }

} else {
    echo "Erreur : formulaire non soumis.";
}
?>