<?php
// db_init.php
$host = "127.0.0.1";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS escuela_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE escuela_db");

    // Crear tabla usuario (ejemplo)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuario (
            ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
            Username VARCHAR(50) UNIQUE NOT NULL,
            Password VARCHAR(255) NOT NULL,
            Rol ENUM('Alumno','Profesor','Admin') NOT NULL,
            ID_Alumno INT NULL,
            ID_Profesor INT NULL
        )
    ");

    echo "✅ Base de datos inicializada correctamente.";
} catch (PDOException $e) {
    die("❌ Error en la inicialización: " . $e->getMessage());
}
?>