<?php
session_start();
require 'db.php'; // tu conexión con $pdo
require 'auth.php';

if (isset($_POST['login'])) {
    $stmt = $pdo->prepare("SELECT * FROM Usuario WHERE Username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['PasswordHash'])) {
        // Guardar el ID del usuario en la sesión
        $_SESSION['user'] = $user['ID_Usuario'];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>