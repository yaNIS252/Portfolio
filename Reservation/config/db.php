<?php
// config/db.php

// Ces informations se trouvent dans ton interface InfinityFree
$host = 'sql303.infinityfree.com'; // Ton MySQL Hostname
$db   = 'if0_41565643_XXX';        // Ton MySQL Database Name
$user = 'if0_41565643';             // Ton MySQL Username
$pass = 'Yaya2006913';    // Ton mot de passe de compte

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // En production, on n'affiche jamais $e->getMessage() aux utilisateurs (faille de sécurité)
     // Pour ton projet pédagogique, on garde l'affichage pour débugger
     die("Erreur de connexion : " . $e->getMessage());
}
?>