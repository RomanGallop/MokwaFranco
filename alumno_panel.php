<?php

session_start();
if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "alumno") {
    header("Location: inicio_sesion.html");
    exit;
}

?>

<h1>Bienvenido, <?php echo $_SESSION['nombre']; ?> <?php echo $_SESSION['apellido']; ?></h1>
<p>Este es tu panel de alumno.</p>
<a href="cerrar_sesion.php">Cerrar sesión</a>