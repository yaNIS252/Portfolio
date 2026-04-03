<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require '../config/db.php';

// Sécurité : Seul un ADMIN peut accéder à cette page
if (!isset($_SESSION['typecompte']) || $_SESSION['typecompte'] !== 'ADM') {
    die("Accès refusé. Espace réservé aux administrateurs.");
}

// 1. RÉCUPÉRATION DES RÉSERVATIONS
// On joint les tables pour avoir le nom du vacancier et le nom du logement
$sql = "SELECT r.*, c.NOMCPTE, h.NOMHEB 
        FROM resa r
        JOIN compte c ON r.USER = c.USER
        JOIN hebergement h ON r.NOHEB = h.NOHEB
        ORDER BY r.DATERESA DESC";
$stmt = $pdo->query($sql);
$reservations = $stmt->fetchAll();

// 2. RÉCUPÉRATION DES ÉTATS POSSIBLES (pour la liste déroulante de modif)
$etats = $pdo->query("SELECT * FROM etat_resa")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Réservations - MyBnB</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<nav class="navbar adm-navbar">
    <a href="dashboard.php" class="logo">MyBnB <span class="badge-admin">ADMIN</span></a>
    <div class="nav-links">
        <a href="gestion_hebergements.php">🏠 Gestion Logements</a>
        <a href="liste_reservations.php">📅 Liste Réservations</a>
        <?php if (isset($_SESSION['nom'])): ?>
            <span class="user-welcome">🛠️ <?php echo htmlspecialchars($_SESSION['nom']); ?></span>
            <a href="../logout.php" class="btn-logout">Déconnexion</a>
        <?php endif; ?>
    </div>
</nav>

<body>
    <h1>📋 Liste des Réservations</h1>


    
    <table border="1" style="width:100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #333; color: white;">
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Hébergement</th>
                <th>Date Début</th>
                <th>État Actuel</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?= $r['NORESA'] ?></td>
                <td><?= htmlspecialchars($r['NOMCPTE']) ?> (<?= $r['USER'] ?>)</td>
                <td><?= htmlspecialchars($r['NOMHEB']) ?></td>
                <td><?= date('d/m/Y', strtotime($r['DATEDEBSEM'])) ?></td>
                <td>
                    <strong><?= $r['CODEETATRESA'] ?></strong>
                </td>
                <td>
                    <td>
    <form action="modifier_statut_resa.php" method="POST" style="display:inline;">
        <input type="hidden" name="noresa" value="<?= $r['NORESA'] ?>">
        <select name="nouveau_statut">
            <?php foreach ($etats as $e): ?>
                <option value="<?= $e['CODEETATRESA'] ?>" <?= ($e['CODEETATRESA'] == $r['CODEETATRESA']) ? 'selected' : '' ?>>
                    <?= $e['NOMETATRESA'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="btn_modifier">Modifier</button>
    </form>

    <form action="supprimer_resa.php" method="POST" style="display:inline;" onsubmit="return confirm('Es-tu sûr de vouloir supprimer cette réservation ?');">
        <input type="hidden" name="noresa" value="<?= $r['NORESA'] ?>">
        <button type="submit" name="btn_supprimer" style="background-color: #e74c3c; color: white; border: none; padding: 5px 10px; cursor: pointer;">
            🗑️ Supprimer
        </button>
    </form>
</td>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>