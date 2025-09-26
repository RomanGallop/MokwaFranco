<?php
// db.php
// Conexión a MySQL en Laragon

$host = "127.0.0.1";   // o "localhost"
$user = "root";        // usuario por defecto en Laragon
$pass = "";            // contraseña vacía en Laragon
$dbname = "escuela_db"; // nombre de tu base de datos

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error en la conexión a la base de datos: " . $e->getMessage());
}
