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

// Vérification méthode POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['email']) && isset($_POST['mot_de_passe'])) {

        $email = trim($_POST['email']); //trim supprime les avant/après 
        $motdepasse = $_POST['mot_de_passe'];

        // Requête préparée sécurisée
        $stmt = $conn->prepare("SELECT id_utilisateur, prenom, mot_de_passe, role FROM utilisateur WHERE email = ?");
        $stmt->bind_param("s", $email); //string pour l'email
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) { //email unique

            $user = $res->fetch_assoc(); //tableau associatif

            // Vérification mot de passe sécurisé
            if (password_verify($motdepasse, $user['mot_de_passe'])) {

                $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
                $_SESSION['role'] = $user['role'];

                echo "<p>Connexion réussie ! Bienvenue " . htmlspecialchars($user['prenom']) . ".</p>";
                echo '<p><a href="index.html">Aller à l’accueil</a></p>';

            } else {
                echo "Mot de passe incorrect.";
            }

        } else {
            echo "Utilisateur non trouvé.";
        }

        $stmt->close();

    } else {
        echo "Erreur : formulaire incomplet.";
    }
}

$conn->close();
?>