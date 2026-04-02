<?php
session_start();
include 'connexion.php';

$login = $_POST['login'];
$password = $_POST['password'];

$query = "SELECT * FROM client WHERE login = :login AND Password = :password";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':login', $login);
$stmt->bindParam(':password', $password);
$stmt->execute();

$result = $stmt->rowCount();

if ($result == 1) {
    $query = "SELECT * FROM client WHERE Login = :login AND Password = :password";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':login', $login);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    
    $_SESSION['login'] = $login;
    $_SESSION['password'] = $password;
    header('location:page_1.html');
} else {
    $_SESSION['badconnecte'] = true;
    header('location:fail.html');
}
?>
