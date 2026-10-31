<?php
$host = "localhost";
$name = "don_pancho";
$user = "GrupoProgra";
$pass = "PrograWeb123";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$name;charset=utf8mb4",$user,$pass,[
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "Conexión exitosa\n";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}