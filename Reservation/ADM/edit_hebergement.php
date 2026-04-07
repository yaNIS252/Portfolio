<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// 1. SÉCURITÉ : Vérification du rang admin
if (!isset($_SESSION['typecompte']) || $_SESSION['typecompte'] !== 'ADM') {
    header("Location: ../login.php");
    exit;
}

// 2. RÉCUPÉRATION DES DONNÉES ACTUELLES
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM HEBERGEMENT WHERE NOHEB = ?");
    $stmt->execute([$id]);
    $h = $stmt->fetch();

    if (!$h) { 
        die("Erreur : Hébergement #$id introuvable en base de données."); 
    }
} else {
    header("Location: gestion_hebergements.php");
    exit;
}

// 3. LOGIQUE DE MISE À JOUR (UPDATE)
if (isset($_POST['btn_modifier'])) {
    $id          = $_POST['NOHEB'];
    $nom         = $_POST['NOMHEB'];
    $type        = $_POST['CODETYPEHEB'];
    $tarif       = $_POST['TARIFSEMHEB'];
    $surface     = $_POST['SURFACEHEB'];   
    $places      = $_POST['NBPLACEHEB'];
    $annee       = $_POST['ANNEEHEB'];
    $secteur     = $_POST['SECTEURHEB'];
    $orientation = $_POST['ORIENTATIONHEB'];
    $internet    = $_POST['INTERNET'];
    $descri      = $_POST['DESCRIHEB'];
    
    // Gestion de la photo : on garde l'ancienne si aucune nouvelle n'est chargée
    $photo = $h['PHOTOHEB']; 
    if (!empty($_FILES['PHOTO']['name'])) {
        $photo = time() . "_" . $_FILES['PHOTO']['name'];
        move_uploaded_file($_FILES['PHOTO']['tmp_name'], "../img/" . $photo);
    }

    $sql = "UPDATE HEBERGEMENT SET 
            NOMHEB = ?, CODETYPEHEB = ?, PHOTOHEB = ?, TARIFSEMHEB = ?, 
            NBPLACEHEB = ?, SURFACEHEB = ?, ANNEEHEB = ?, SECTEURHEB = ?, 
            ORIENTATIONHEB = ?, INTERNET = ?, DESCRIHEB = ? 
            WHERE NOHEB = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $type, $photo, $tarif, $places, $surface, $annee, $secteur, $orientation, $internet, $descri, $id]);

    // Redirection avec message de succès
    header("Location: gestion_hebergements.php?msg=updated");
    exit;
}

// Récupération des types pour la liste déroulante
$types = $pdo->query("SELECT * FROM type_heb")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Hébergement - MyBnB</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar adm-navbar">
    <a href="dashboard.php" class="logo">MyBnB <span class="badge-admin">ADMIN</span></a>
    <div class="nav-links">
        <a href="gestion_hebergements.php">🏠 Retour Gestion</a>
    </div>
</nav>

<div class="container">
    <h1>Modifier le logement #<?= $h['NOHEB'] ?></h1>

    <div class="form-container" style="background: #f4f4f4; padding: 25px; border-radius: 10px; border: 1px solid #ccc;">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="NOHEB" value="<?= $h['NOHEB'] ?>">

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                <div>
                    <label>Nom du logement</label>
                    <input type="text" name="NOMHEB" value="<?= htmlspecialchars($h['NOMHEB']) ?>" required style="width:100%">
                </div>
                <div>
                    <label>Type</label>
                    <select name="CODETYPEHEB" required style="width:100%">
                        <?php foreach($types as $t): ?>
                            <option value="<?= $t['CODETYPEHEB'] ?>" <?= ($t['CODETYPEHEB'] == $h['CODETYPEHEB']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['NOMTYPEHEB']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Tarif Semaine (€)</label>
                    <input type="number" step="0.01" name="TARIFSEMHEB" value="<?= $h['TARIFSEMHEB'] ?>" required style="width:100%">
                </div>
                <div>
                    <label>Nb de places</label>
                    <input type="number" name="NBPLACEHEB" value="<?= $h['NBPLACEHEB'] ?>" style="width:100%">
                </div>
                <div>
                    <label>Surface (m²)</label>
                    <input type="number" name="SURFACEHEB" value="<?= $h['SURFACEHEB'] ?>" style="width:100%">
                </div>
                <div>
                    <label>Année</label>
                    <input type="number" name="ANNEEHEB" value="<?= $h['ANNEEHEB'] ?>" style="width:100%">
                </div>
                <div>
                    <label>Secteur</label>
                    <input type="text" name="SECTEURHEB" value="<?= htmlspecialchars($h['SECTEURHEB'] ?? '') ?>" style="width:100%">
                </div>
                <div>
                    <label>Orientation</label>
                    <select name="ORIENTATIONHEB" style="width:100%">
                        <?php $o = $h['ORIENTATIONHEB']; ?>
                        <option value="SUD" <?= $o == 'SUD' ? 'selected' : '' ?>>SUD</option>
                        <option value="NORD" <?= $o == 'NORD' ? 'selected' : '' ?>>NORD</option>
                        <option value="EST" <?= $o == 'EST' ? 'selected' : '' ?>>EST</option>
                        <option value="OUEST" <?= $o == 'OUEST' ? 'selected' : '' ?>>OUEST</option>
                    </select>
                </div>
                <div>
                    <label>Internet</label>
                    <select name="INTERNET" style="width:100%">
                        <option value="1" <?= $h['INTERNET'] == 1 ? 'selected' : '' ?>>Oui</option>
                        <option value="0" <?= $h['INTERNET'] == 0 ? 'selected' : '' ?>>Non</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:15px;">
                <label>Description</label>
                <textarea name="DESCRIHEB" rows="3" style="width:100%; resize: none;"><?= htmlspecialchars($h['DESCRIHEB']) ?></textarea>
            </div>

            <div style="margin-top:15px; display: flex; align-items: center; gap: 20px;">
                <div style="flex:1;">
                    <label>Photo actuelle : <strong><?= $h['PHOTOHEB'] ?></strong></label><br>
                    <input type="file" name="PHOTO">
                </div>
                <button type="submit" name="btn_modifier" style="background: #27ae60; color:white; border:none; padding:15px 30px; border-radius:5px; cursor:pointer; font-weight:bold;">
                    ENREGISTRER LES MODIFICATIONS
                </button>
                <a href="gestion_hebergements.php" style="color:#666; text-decoration:none;">Annuler</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>