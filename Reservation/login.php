
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
}
require 'config/db.php';
/* ---------------------------------------------------------
   PARTIE 1 : LOGIQUE DE TRAITEMENT (Le "Cerveau")
   On vérifie si le formulaire a été soumis
   --------------------------------------------------------- */



$error = null;

if (isset($_POST['btn_connect'])) {
    $user = trim($_POST['user']);
    $mdp  = trim($_POST['mdp']);

    $sql = "SELECT * FROM COMPTE WHERE USER = :u";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['u' => $user]);
    $compte = $stmt->fetch();

    if (!$compte) {
        $error = "ERREUR : L'utilisateur '$user' n'existe pas.";
    } 
    elseif ($compte['DATEFERME'] !== null) {
        $error = "ERREUR : Le compte '$user' est désactivé.";
    }
    elseif ($mdp !== $compte['MDP']) {
        $error = "ERREUR : Mot de passe incorrect.";
    }
    else {
        // SYNCHRONISATION : On utilise 'user'
        $_SESSION['user']       = $compte['USER'];
        $_SESSION['nom']        = $compte['NOMCPTE'];
        $_SESSION['typecompte'] = $compte['TYPECOMPTE']; 
        
        if ($compte['TYPECOMPTE'] === 'ADM') {
            header("Location: ADM/dashboard.php");
        } else {
            header("Location: VAC/menu.php");
        }
        exit();
    }
}

$is_connected = isset($_SESSION['user']);
$user_data = null;

if ($is_connected) {
    $stmt = $pdo->prepare("SELECT NOMCPTE, PRENOMCPTE, ADRMAILCPTE, TYPECOMPTE, DATEINSCRIP FROM COMPTE WHERE USER = ?");
    $stmt->execute([$_SESSION['user']]);
    $user_data = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Espace - MyBnB</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

    <nav class="navbar">
        <a href="index.php" class="logo">MyBnB</a>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
        </div>
    </nav>

    <main class="auth-container">
        
        <?php if (!$is_connected): ?>
            <div class="auth-card">
                <h1>Connexion</h1>
                
                <?php if (isset($error)): ?>
                    <div class="error-banner"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="auth-form">
                    <div class="input-group">
                        <label>Identifiant</label>
                        <input type="text" name="user" required>
                    </div>
                    <div class="input-group">
                        <label>Mot de passe</label>
                        <input type="password" name="mdp" required>
                    </div>
                    <button type="submit" name="btn_connect" class="btn-primary-full">Se connecter</button>
                </form>
            </div>

        <?php else: ?>
            <div class="auth-card profile-card">
                <div class="profile-header">
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($user_data['PRENOMCPTE'], 0, 1)); ?>
                    </div>
                    <h2>Bonjour, <?php echo htmlspecialchars($user_data['PRENOMCPTE']); ?></h2>
                </div>

                <div class="profile-details">
                    <div class="detail-item"><strong>Nom :</strong> <span><?php echo htmlspecialchars($user_data['NOMCPTE']); ?></span></div>
                    <div class="detail-item"><strong>Email :</strong> <span><?php echo htmlspecialchars($user_data['ADRMAILCPTE']); ?></span></div>
                    <div class="detail-item"><strong>Type :</strong> <span><?php echo $user_data['TYPECOMPTE']; ?></span></div>
                </div>

                <a href="logout.php" class="btn-logout-action">Déconnexion</a>
                <a href="index.php" class="btn-secondary">Retour à l'accueil</a>
            </div>
        <?php endif; ?>

    </main>

    <footer class="main-footer">
        <p>© 2026 MyBnB - Yanis BTS SIO</p>
    </footer>

</body>
</html>