<?php
$host = getenv('DB_HOST');
$name = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$name", $user, $pass);
    echo "Conectado a MySQL";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}