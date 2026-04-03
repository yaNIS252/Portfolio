<?php
// seul moyen pour de se souvenir qui est connecté.
session_start();
require '../config/db.php';

/* 
   On récupère l'ID via $_GET. On utilise intval() car un ID est TOUJOURS un entier.
*/
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Location: dashboard.php'); // Redirection si l'ID est foireux
    exit;
}

/* 
   JAMAIS $id directement "?" est un espace réservé.
   PDO va nettoyer avant de l'envoyer Protection Injection SQL.
*/
$stmt = $pdo->prepare("SELECT * FROM HEBERGEMENT WHERE NOHEB = ?");
$stmt->execute([$id]);
$h = $stmt->fetch();

// NOTE : Si fetch() ne renvoie rien, c'est que l'ID n'existe pas en base.
if (!$h) { die("Erreur critique : Cet hébergement n'existe pas."); }

$user_name = isset($_SESSION['nom']) ? $_SESSION['nom'] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($h['NOMHEB']); ?> - MyBnB</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-details">

    <nav class="navbar adm-navbar">
    <a href="menu.php" class="logo">MyBnB <span class="badge-admin">ADMIN</span></a>
    
    <div class="nav-links">
        <a href="gestion_hebergements.php">🏠 Gestion Logements</a>
        
        <a href="liste_reservations.php">📅 Liste Réservations</a>

        <?php if (isset($_SESSION['nom'])): ?>
            <span class="user-welcome">🛠️ <?php echo htmlspecialchars($_SESSION['nom']); ?></span>
            <a href="../logout.php" class="btn-logout">Déconnexion</a>
        <?php else: ?>
            <a href="../login.php" class="btn-login">Connexion</a>
        <?php endif; ?>
    </div>
</nav>

    <main class="detail-container">
        <header class="detail-header">
            <h1><?php echo htmlspecialchars($h['NOMHEB']); ?></h1>
            <p>📍 <?php echo htmlspecialchars($h['SECTEURHEB']); ?></p>
        </header>

        <div class="detail-hero">
            <img src="../img/<?php echo htmlspecialchars($h['PHOTOHEB']); ?>" alt="Logement">
        </div>

        <div class="detail-grid">
            <div class="info-column">
                <section class="characteristics">
                    <h2>Caractéristiques</h2>
                    <div class="tags">
                        <span>👥 <?php echo $h['NBPLACEHEB']; ?> places</span>
                        <span>📐 <?php echo $h['SURFACEHEB']; ?> m²</span>
                        <span>📶 Wi-Fi : <?php echo $h['INTERNET'] ? 'Oui' : 'Non'; ?></span>
                    </div>
                </section>

                <section class="description">
                    <h3>À propos</h3>
                    <p><?php echo nl2br(htmlspecialchars($h['DESCRIHEB'])); ?></p>
                </section>
            </div>

            <aside class="booking-widget">
                <div class="sticky-card">
                    <p class="price"><span><?php echo number_format($h['TARIFSEMHEB'], 0, ',', ' '); ?> €</span> / semaine</p>
                    
                    <form action="traitement_resa.php" method="POST">
                        <input type="hidden" name="noheb" value="<?php echo $h['NOHEB']; ?>">
                        
                        <label>Disponibilités :</label>
                        <select name="datedebsem" required>
                            <option value="">-- Choisir un samedi --</option>
                            <?php
                            /* LOGIQUE DE DISPONIBILITÉ (Le "cerveau" du site) :
                               On veut les semaines qui ne sont PAS dans la table RESA pour cet ID.
                               "NOT IN" permet d'exclure les dates déjà prises.
                            */
                            $sql_dispo = "SELECT DATEDEBSEM FROM SEMAINE 
                                          WHERE DATEDEBSEM NOT IN (
                                              SELECT DATEDEBSEM FROM RESA WHERE NOHEB = :id
                                          ) 
                                          ORDER BY DATEDEBSEM ASC";
                            
                            $stmt_dispo = $pdo->prepare($sql_dispo);
                            $stmt_dispo->execute(['id' => $id]);

                            while($row = $stmt_dispo->fetch()) {
                                // Conversion date US (YYYY-MM-DD) -> FR (DD/MM/YYYY)
                                $date_fr = date('d/m/Y', strtotime($row['DATEDEBSEM']));
                                echo "<option value='".$row['DATEDEBSEM']."'>Samedi ".$date_fr."</option>";
                            }
                            ?>
                        </select>

                        <?php if (isset($_SESSION['user'])): ?>
                            <button type="submit" class="btn-reserve">Réserver ce séjour</button>
                        <?php else: ?>
                            <a href="../login.php" class="btn-reserve" style="text-align:center; text-decoration:none; display:block;">Connectez-vous pour réserver</a>
                        <?php endif; ?>
                    </form>
                </div>
            </aside>
        </div>
    </main>

    <footer class="main-footer">
        <p>© 2026 MyBnB - Yanis SIO SLAM</p>
    </footer>

</body>
</html>