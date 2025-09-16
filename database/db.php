<?php
$host = 'mysql.hostinger.com';
$db = 'u164511188_apartmenthub';
$user = 'u164511188_apthub';
$pass = 'Apartmenthub@01';
//db password: apartmenthub@01
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}



?>
