<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['typecompte']) || $_SESSION['typecompte'] !== 'ADM') {
    header("Location: ../login.php");
    exit;
}

// --- 1. LOGIQUE SUPPRESSION ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM HEBERGEMENT WHERE NOHEB = ?");
    $stmt->execute([$id]);
    // REDIRECTION AVEC MESSAGE
    header("Location: gestion_hebergements.php?msg=deleted");
    exit;
}

// --- 2. LOGIQUE AJOUT ---
if (isset($_POST['btn_ajouter'])) {
    $nom         = $_POST['NOMHEB'];
    $type        = $_POST['CODETYPEHEB'];
    $tarif       = $_POST['TARIFSEMHEB'];
    $surface     = $_POST['SURFACEHEB'] ?? 0;   
    $places      = $_POST['NBPLACEHEB'] ?? 0;
    $annee       = $_POST['ANNEEHEB'] ?? date('Y');
    $secteur     = $_POST['SECTEURHEB'] ?? '';
    $orientation = $_POST['ORIENTATIONHEB'] ?? 'SUD';
    $internet    = $_POST['INTERNET'] ?? 0;
    $descri      = $_POST['DESCRIHEB'] ?? '';
    
    $photo = "default.jpg"; 
    if (!empty($_FILES['PHOTO']['name'])) {
        $photo = time() . "_" . $_FILES['PHOTO']['name'];
        if(!is_dir("../img/")) { mkdir("../img/"); }
        move_uploaded_file($_FILES['PHOTO']['tmp_name'], "../img/" . $photo);
    }

    $sql = "INSERT INTO HEBERGEMENT 
            (NOMHEB, CODETYPEHEB, PHOTOHEB, TARIFSEMHEB, NBPLACEHEB, SURFACEHEB, ANNEEHEB, SECTEURHEB, ORIENTATIONHEB, INTERNET, DESCRIHEB, ETATHEB) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Disponible')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $type, $photo, $tarif, $places, $surface, $annee, $secteur, $orientation, $internet, $descri]);
    
    // REDIRECTION AVEC MESSAGE
    header("Location: gestion_hebergements.php?msg=added");
    exit;
}

// --- 3. RÉCUPÉRATION DES DONNÉES ---
$logements = $pdo->query("SELECT h.*, t.NOMTYPEHEB 
                          FROM HEBERGEMENT h 
                          LEFT JOIN type_heb t ON h.CODETYPEHEB = t.CODETYPEHEB 
                          ORDER BY h.NOHEB DESC")->fetchAll(PDO::FETCH_ASSOC);

$types = $pdo->query("SELECT * FROM type_heb")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - MyBnB</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .adm-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .adm-table th, .adm-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .adm-table th { background-color: #333; color: white; }
        .form-container { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .btn-suppr { color: #e74c3c; text-decoration: none; font-weight: bold; }
        /* Style des messages */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

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

<div class="container">
    <h1>🛠️ Gestion des Hébergements</h1>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'added'): ?>
            <div class="alert alert-success">✅ L'hébergement a été ajouté avec succès.</div>
        <?php elseif ($_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success">🔄 Les modifications ont été enregistrées.</div>
        <?php elseif ($_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-danger">🗑️ L'hébergement a été supprimé.</div>
        <?php endif; ?>
    <?php endif; ?>²

    <div class="form-container" style="background: #f4f4f4; padding: 25px; border-radius: 10px; border: 1px solid #ccc;">
        <h3 style="margin-top:0;">➕ Ajouter un nouvel hébergement</h3>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                <div>
                    <label>Nom du logement</label>
                    <input type="text" name="NOMHEB" required style="width:100%">
                </div>
                <div>
                    <label>Type</label>
                    <select name="CODETYPEHEB" required style="width:100%">
                        <?php foreach($types as $t): ?>
                            <option value="<?= $t['CODETYPEHEB'] ?>"><?= htmlspecialchars($t['NOMTYPEHEB']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Tarif Semaine (€)</label>
                    <input type="number" step="0.01" name="TARIFSEMHEB" required style="width:100%">
                </div>
                <div>
                    <label>Nb de places</label>
                    <input type="number" name="NBPLACEHEB" style="width:100%">
                </div>
                <div>
                    <label>Surface (m²)</label>
                    <input type="number" name="SURFACEHEB" style="width:100%">
                </div>
                <div>
                    <label>Année</label>
                    <input type="number" name="ANNEEHEB" style="width:100%">
                </div>
                <div>
                    <label>Secteur</label>
                    <input type="text" name="SECTEURHEB" style="width:100%">
                </div>
                <div>
                    <label>Orientation</label>
                    <select name="ORIENTATIONHEB" style="width:100%">
                        <option value="SUD">SUD</option>
                        <option value="NORD">NORD</option>
                        <option value="EST">EST</option>
                        <option value="OUEST">OUEST</option>
                    </select>
                </div>
                <div>
                    <label>Internet</label>
                    <select name="INTERNET" style="width:100%">
                        <option value="1">Oui</option>
                        <option value="0">Non</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:15px;">
                <label>Description</label>
                <textarea name="DESCRIHEB" rows="3" style="width:100%; resize: none;"></textarea>
            </div>

            <div style="margin-top:15px; display: flex; align-items: center; gap: 20px;">
                <div style="flex:1;">
                    <label>Photo</label>
                    <input type="file" name="PHOTO">
                </div>
                <button type="submit" name="btn_ajouter" style="background: #27ae60; color:white; border:none; padding:15px 30px; border-radius:5px; cursor:pointer;">
                    CRÉER L'HÉBERGEMENT
                </button>
            </div>
        </form>
    </div>

    <table class="adm-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Nom</th>
                <th>Type</th>
                <th>Prix</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logements as $l): ?>
            <tr>
                <td><?= $l['NOHEB'] ?></td>
                <td><img src="../img/<?= $l['PHOTOHEB'] ?>" width="60" style="border-radius:4px;"></td>
                <td><?= htmlspecialchars($l['NOMHEB']) ?></td>
                <td><?= htmlspecialchars($l['NOMTYPEHEB'] ?? 'Inconnu') ?></td>
                <td><?= number_format($l['TARIFSEMHEB'], 2) ?> €</td>
                <td>
                    <a href="edit_hebergement.php?id=<?= $l['NOHEB'] ?>">✏️</a> | 
                    <a href="?delete=<?= $l['NOHEB'] ?>" class="btn-suppr" onclick="return confirm('Supprimer ?')">🗑️</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>