<?php
session_start();
require 'config/db.php';

// Récupération du nom de l'utilisateur pour la navbar
$user_name = isset($_SESSION['nom']) ? $_SESSION['nom'] : null;

// Requête pour récupérer les hébergements (Colonnes selon ton SQL)
// On récupère NOHEB, NOMHEB, SECTEURHEB, TARIFSEMHEB et PHOTOHEB
$query = "SELECT NOHEB, NOMHEB, SECTEURHEB, TARIFSEMHEB, PHOTOHEB FROM HEBERGEMENT";
$stmt = $pdo->query($query);
$hebergements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyBnB - Voyagez comme chez vous</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">MyBnB</a>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="#">Destinations</a>
            <a href="#">Mes réservations</a>
            <?php if ($user_name): ?>
                <span class="user-welcome">Bonjour, <?php echo htmlspecialchars($user_name); ?></span>
                <a href="logout.php" class="btn-login">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Connexion</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-overlay">
            <h1>Voyagez comme chez vous</h1>
            <p>Trouvez des logements uniques et des expériences locales.</p>
            <div class="search-container">
                <input type="text" placeholder="Où allez-vous ?" class="search-input">
                <button class="search-button">Rechercher</button>
            </div>
        </div>
    </header>

    <main class="container">
        <h2 class="section-title">Logements populaires</h2>
        
        <div class="accommodation-grid">
            <?php foreach ($hebergements as $h): ?>
                <a href="details.php?id=<?php echo $h['NOHEB']; ?>" class="card">
                    <div class="card-image">
                        <img src="img/<?php echo htmlspecialchars($h['PHOTOHEB']); ?>" alt="<?php echo htmlspecialchars($h['NOMHEB']); ?>">
                    </div>
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($h['NOMHEB']); ?></h3>
                        <p class="location"><?php echo htmlspecialchars($h['SECTEURHEB']); ?></p>
                        <p class="price"><strong><?php echo number_format($h['TARIFSEMHEB'], 0, ',', ' '); ?>€</strong>/semaine</p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="main-footer">
        <p>© 2025 MyBnB - Tous droits réservés</p>
    </footer>

</body>
</html>