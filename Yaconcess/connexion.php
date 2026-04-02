<?php

$dsn= 'mysql:host=localhost;dbname=yaconcess';
$DBusername = 'root';
$DBpassword = '';

try {
    $pdo = new PDO($dsn, $DBusername, $DBpassword);
} catch(PDOException $e) {
    die("Erreur de connexion : ".$e->getMessage());
}
?>
