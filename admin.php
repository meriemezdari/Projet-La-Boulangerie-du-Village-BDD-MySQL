<?php
// message d'erreur
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// démarrage session
session_start();

// connexion à la base de données
$servername = "db";
$username = "root";
$password = "root";
$dbname = "boulangerie";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// si formulaire de connexion soumis
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'], $_POST['mot_de_passe'])) {

    $email = trim($_POST['email']);
    $motdepasse = $_POST['mot_de_passe'];

    $stmt = $conn->prepare("SELECT id_utilisateur, prenom, mot_de_passe, role FROM utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($motdepasse, $user['mot_de_passe']) && $user['role'] === 'admin') {
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            $_SESSION['role'] = 'admin';
        } else {
            $erreur = "Email ou mot de passe incorrect, ou vous n'êtes pas admin.";
        }
    } else {
        $erreur = "Utilisateur non trouvé.";
    }
    $stmt->close();
}

// si admin connecté
$admin_connecte = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion des commandes</title>
    <link rel="stylesheet" href="style.css">
    <style>
        main { max-width: 1200px; margin: 40px auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #8D3D1A; text-align: center; }
        th { background-color: #EFBE84; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        a { text-decoration: none; color: #8D3D1A; font-weight: bold; }
        a:hover { color: #6b2c0f; }
        h1 { text-align: center; color: #8D3D1A; font-family: 'Pacifico', cursive; }
        form { max-width: 400px; margin: 20px auto; display: flex; flex-direction: column; }
        input { margin-bottom: 10px; padding: 8px; font-size: 1em; }
        button { padding: 8px; font-size: 1em; background-color: #8D3D1A; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #6b2c0f; }
    </style>
</head>
<body>
<header>
    <h1>Administration - Gestion des commandes</h1>
</header>
<main>

<?php if (!$admin_connecte) : ?>
    <?php if (!empty($erreur)) : ?>
        <p style="color:red; text-align:center;"><?= htmlspecialchars($erreur); ?></p>
    <?php endif; ?>
    <form action="admin.php" method="post">
        <label for="email">Email :</label>
        <input type="email" name="email" id="email" required>
        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" name="mot_de_passe" id="mot_de_passe" required>
        <button type="submit">Se connecter</button>
    </form>

<?php else: ?>
    <?php
    // Mise à jour du statut
    if(isset($_GET['id_commande'], $_GET['statut'])){
        $id_commande = (int)$_GET['id_commande'];
        $statut = $_GET['statut'];
        $statuts_valides = ['en cours', 'prête', 'livrée'];
        if(in_array($statut, $statuts_valides)){
            $stmt_update = $conn->prepare("UPDATE commande SET statut = ? WHERE id_commande = ?");
            $stmt_update->bind_param("si", $statut, $id_commande);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }

    // Récupération des commandes avec requête préparée
    $result = $conn->query("
        SELECT commande.id_commande, utilisateur.prenom, utilisateur.nom, commande.date_livraison, 
               commande.adresse_livraison AS adresse, commande.ville, commande.code_postal, commande.statut
        FROM commande
        JOIN utilisateur ON commande.id_utilisateur = utilisateur.id_utilisateur
        ORDER BY commande.id_commande DESC
    ");
    ?>

    <?php if($result && $result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Date Livraison</th>
                <th>Adresse</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$row['id_commande'] ?></td>
                    <td><?= htmlspecialchars($row['prenom'].' '.$row['nom']) ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['date_livraison']))) ?></td>
                    <td><?= htmlspecialchars($row['adresse'].', '.$row['ville'].' '.$row['code_postal']) ?></td>
                    <td><?= htmlspecialchars($row['statut']) ?></td>
                    <td>
                        <?php
                        if ($row['statut'] != 'en cours') echo '<a href="admin.php?id_commande=' . $row['id_commande'] . '&statut=en cours">en cours</a><br>';
                        if ($row['statut'] != 'prête') echo '<a href="admin.php?id_commande=' . $row['id_commande'] . '&statut=prête">prête</a><br>';
                        if ($row['statut'] != 'livrée') echo '<a href="admin.php?id_commande=' . $row['id_commande'] . '&statut=livrée">livrée</a><br>';
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Aucune commande pour le moment.</p>
    <?php endif; ?>
<?php endif; ?>

</main>
</body>
</html>

<?php $conn->close(); ?>