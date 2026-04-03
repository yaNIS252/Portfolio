<?php
// NOTE APPRENTISSAGE : Pour détruire une session, il faut d'abord l'ouvrir.
session_start();

// On vide toutes les variables de session ($_SESSION devient un tableau vide)
$_SESSION = array();

// On détruit physiquement le fichier de session sur le serveur
session_destroy();

// redirige vers l'index avec un message de confirmation dans l'URL
header("Location: index.php?logout=success");
exit();
?>