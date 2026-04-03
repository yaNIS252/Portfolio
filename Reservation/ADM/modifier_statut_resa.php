<?php
session_start();
require '../config/db.php';

if (isset($_POST['btn_modifier'])) {
    $noresa = intval($_POST['noresa']);
    $statut = $_POST['nouveau_statut'];

    try {
        $sql = "UPDATE resa SET CODEETATRESA = ? WHERE NORESA = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$statut, $noresa]);

        header("Location: liste_reservations.php?update=ok");
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de la mise à jour : " . $e->getMessage());
    }
}