<?php
@include("connexion.php");


$a = $_POST["login"];
$b = $_POST["mdp"];
$c = $_POST["fonction"];


$reql = "INSERT INTO user(login,mdp,fonction) 
VALUES ('$a', '$b', '$c')";


if (mysqli_query($conn, $reql)) {
    echo "<center><p>Enregistrement effectué</p></center>";
} else {
    echo "<center><p>Erreur: " . mysqli_error($conn) . "</p></center>";
}


header("location:index.html");

mysqli_close($conn);
?>
