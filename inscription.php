<?php
// message d'erreur
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Vérification si formulaire envoyé
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Vérification que tous les champs existent
    if (
        isset($_POST['nom']) &&
        isset($_POST['prenom']) &&
        isset($_POST['email']) &&
        isset($_POST['mot_de_passe']) &&
        isset($_POST['confirmation_mdp'])
    ) {

        $nom = trim($_POST['nom']); //trim supprime espace avant/après
        $prenom = trim($_POST['prenom']); //trim supprime espace avant/après
        $email = trim($_POST['email']); //trim supprime espace avant/après
        $motdepasse = $_POST['mot_de_passe'];
        $confirmation = $_POST['confirmation_mdp'];

        // Vérification mots de passe identiques
        if ($motdepasse !== $confirmation) {
            die("Les mots de passe ne correspondent pas.");
        }

        // Vérification email déjà existant
        $stmt_check = $conn->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $res = $stmt_check->get_result();

        if ($res->num_rows > 0) {
            $stmt_check->close();
            die("Un compte avec cet email existe déjà.");
        }

        $stmt_check->close();

        // Hachage du mot de passe
        $hash_mdp = password_hash($motdepasse, PASSWORD_DEFAULT);

        // Insertion utilisateur
        $stmt = $conn->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'client')");
        $stmt->bind_param("ssss", $nom, $prenom, $email, $hash_mdp);

        if ($stmt->execute()) {
            echo "<p>Inscription réussie ! Vous pouvez maintenant vous connecter.</p>";
            echo '<p><a href="inscription.html">Retour à la connexion</a></p>';
        } else {
            echo "Erreur lors de l'inscription.";
        }

        $stmt->close();

    } else {
        echo "Tous les champs doivent être remplis.";
    }

}

$conn->close();
?>