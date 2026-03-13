<?php
// message d'erreur
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session
session_start();

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

// Récupération du panier
$panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : array();
$total = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Panier - La Boulangerie du Village</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="barre-haut">
        <a href="index.html">
            <img src="logo_boulangerie.png" alt="Logo" class="logo">
        </a>
        <h1 class="titre">La Boulangerie du Village</h1>
        <a href="inscription.html">
            <img src="logo-inscription.png" alt="Inscription/Connexion" class="inscription">
        </a>
    </div>
    <nav class="barre-nav">
        <ul>
            <li><a href="index.html">Accueil</a></li>
            <li><a href="page-produit.html">Produit</a></li>
            <li><a href="inscription.html">Inscription</a></li>
            <li><a href="panier.php">Panier</a></li>
            <li><a href="informations-pratiques.html">Informations Pratiques</a></li>
        </ul>
    </nav>
</header>
<main>
<section class="panier-container">
    <div class="panier-recap">
        <h2>Mon Panier</h2>

        <?php if (empty($panier)) : ?>
            <p>Votre panier est vide.</p>
        <?php else : ?>
            <?php foreach ($panier as $id => $quantite) : ?>
                <?php
                // Forcer les types pour éviter les erreurs
                $id = (int)$id;
                $quantite = (int)$quantite;

                // Requête préparée sécurisée
                $stmt = $conn->prepare("SELECT nom, prix FROM produit WHERE id_produit = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $produit = $result->fetch_assoc();
                    $prix_unitaire = (float)$produit['prix']; // convertir en float
                    $total_ligne = $prix_unitaire * $quantite;
                    $total += $total_ligne;
                ?>
                    <div class="produit-panier">
                        <p>
                            <?= htmlspecialchars($produit['nom']); ?>
                            - <?= htmlspecialchars(number_format($prix_unitaire, 2, ',', ' ')); ?>€
                            x <?= htmlspecialchars($quantite); ?>
                            = <?= htmlspecialchars(number_format($total_ligne, 2, ',', ' ')); ?>€
                        </p>
                    </div>
                <?php
                }
                $stmt->close();
                ?>
            <?php endforeach; ?>

            <p class="total">Total : <?= htmlspecialchars(number_format($total, 2, ',', ' ')); ?>€</p>
        <?php endif; ?>
    </div>

    <div class="panier-validation">
        <h2>Validation</h2>
        <form action="validation_commande.php" method="post">
            <label for="date">Date de livraison :</label>
            <input type="date" id="date" name="date" required>

            <label for="adresse">Adresse :</label>
            <input type="text" id="adresse" name="adresse" required>

            <label for="ville">Ville :</label>
            <input type="text" id="ville" name="ville" required>

            <label for="codepostal">Code Postal :</label>
            <input type="text" id="codepostal" name="codepostal" required>

            <button type="submit">Valider la commande</button>
        </form>
    </div>
</section>
</main>
<footer>
    <p>&copy; 2026 La Boulangerie du Village. Tous droits réservés.</p>
</footer>
</body>
</html>
<?php
$conn->close();
?>