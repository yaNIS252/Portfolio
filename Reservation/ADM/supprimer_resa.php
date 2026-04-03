<?php
session_start();
require '../config/db.php';

// Sécurité : Seul l'ADMIN peut supprimer
if (!isset($_SESSION['typecompte']) || $_SESSION['typecompte'] !== 'ADM') {
    die("Action interdite.");
}

if (isset($_POST['btn_supprimer'])) {
    $noresa = intval($_POST['noresa']);

    try {
        // La requête de suppression basée sur la clé primaire
        $sql = "DELETE FROM resa WHERE NORESA = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$noresa]);

        // Retour à la liste avec un message de succès
        header("Location: liste_reservations.php?delete=success");
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
} else {
    header("Location: liste_reservations.php");
    exit;
}