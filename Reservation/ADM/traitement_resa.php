<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config/db.php';

// 1. SÉCURITÉ : On vérifie si l'utilisateur est bien là
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php?error=expired");
    exit;
}

$user_id = $_SESSION['user']; 

if (isset($_POST['noheb'], $_POST['datedebsem'])) {
    $noheb   = intval($_POST['noheb']);
    $datedeb = $_POST['datedebsem'];

    // Récupération des infos du logement (Tarif et Places)
    $stmt_heb = $pdo->prepare("SELECT TARIFSEMHEB, NBPLACEHEB FROM HEBERGEMENT WHERE NOHEB = ?");
    $stmt_heb->execute([$noheb]);
    $heb = $stmt_heb->fetch();

    if ($heb) {
        try {
            // 2. INSERTION DANS LA TABLE 'resa'
            $sql = "INSERT INTO resa (
                        USER, 
                        DATEDEBSEM, 
                        NOHEB, 
                        CODEETATRESA, 
                        DATERESA, 
                        MONTANTARRHES, 
                        NBOCCUPANT, 
                        TARIFSEMRESA
                    ) VALUES (?, ?, ?, 'ATT', ?, ?, ?, ?)";
            
            $stmt_ins = $pdo->prepare($sql);
            
            $date_resa = date('Y-m-d'); 
            $arrhes    = $heb['TARIFSEMHEB'] * 0.30; 
            
            $stmt_ins->execute([
                $user_id,
                $datedeb,
                $noheb,
                $date_resa, 
                $arrhes, 
                $heb['NBPLACEHEB'], 
                $heb['TARIFSEMHEB']
            ]);

            header("Location: dashboard.php?res=success");
            exit;

        } catch (PDOException $e) {
            
            die("Erreur de liaison BDD : " . $e->getMessage());
        }
    } else {
        die("Erreur : Logement introuvable.");
    }
}