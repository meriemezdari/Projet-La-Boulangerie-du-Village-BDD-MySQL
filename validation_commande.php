<?php
// message d'erreur
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de session
session_start();

// Vérification si l'utilisateur est connecté
if(!isset($_SESSION['id_utilisateur'])){
    header("Location: inscription.html");
    exit();
}

// Connexion à la base de données
$servername = "db";
$username = "root";
$password = "root";
$dbname = "boulangerie";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// Vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérification du formulaire et du panier
if($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_SESSION['panier'])) {

    // Récupération des informations du formulaire avec sécurisation
    $id_utilisateur = $_SESSION['id_utilisateur'];
    $date_livraison = $_POST['date'];
    $adresse = trim($_POST['adresse']); //trim supprime les avant/après 
    $ville = trim($_POST['ville']); //trim supprime les avant/après 
    $codepostal = trim($_POST['codepostal']); //trim supprime les avant/après 
    $statut = 'en cours';

    // Calcul du total de la commande
    $total = 0;
    foreach ($_SESSION['panier'] as $id => $quantite) {
        $stmt_prix = $conn->prepare("SELECT prix FROM produit WHERE id_produit = ?");
        $stmt_prix->bind_param("i", $id);
        $stmt_prix->execute();
        $result = $stmt_prix->get_result();
        if($result && $result->num_rows > 0){
            $produit = $result->fetch_assoc();
            $total += $produit['prix'] * $quantite;
        }
        $stmt_prix->close();
    }

    // Insertion de la commande avec NOW() pour la date
    $stmt_commande = $conn->prepare("
        INSERT INTO commande 
        (id_utilisateur, date_commande, date_livraison, adresse_livraison, ville, code_postal, statut, total) 
        VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)
    ");
    $stmt_commande->bind_param("isssssd", $id_utilisateur, $date_livraison, $adresse, $ville, $codepostal, $statut, $total);

    if ($stmt_commande->execute()) {

        $id_commande = $stmt_commande->insert_id;

        // Insertion des produits de la commande
        $stmt_produit = $conn->prepare("
            INSERT INTO ligne_commande (id_commande, id_produit, quantite, prix_unitaire) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($_SESSION['panier'] as $id => $quantite) {
            $stmt_prix = $conn->prepare("SELECT prix FROM produit WHERE id_produit = ?");
            $stmt_prix->bind_param("i", $id);
            $stmt_prix->execute();
            $result = $stmt_prix->get_result();
            $prix_unitaire = 0;
            if($result && $result->num_rows > 0) {
                $prix_unitaire = $result->fetch_assoc()['prix'];
            }
            $stmt_prix->close();

            $stmt_produit->bind_param("iiid", $id_commande, $id, $quantite, $prix_unitaire);
            $stmt_produit->execute();
        }

        $stmt_produit->close();
        $stmt_commande->close();

        // Vider le panier
        unset($_SESSION['panier']);

        // Message de confirmation sécurisé
        echo "<p style='color:green;'>Commande validée ! Merci pour votre achat.</p>";
        echo '<p><a href="index.html">Retour à l’accueil</a></p>';

    } else {
        echo "<p style='color:red;'>Erreur lors de la commande : " . htmlspecialchars($stmt_commande->error) . "</p>";
        $stmt_commande->close();
    }

} else {
    echo "<p style='color:red;'>Votre panier est vide ou le formulaire n'a pas été soumis.</p>";
}

$conn->close();
?>