<?php 
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'face_detective';

$dsn = "mysql:host=$host;dbname=$dbname";

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Unable to connect with the database: ' . $e->getMessage());
}
