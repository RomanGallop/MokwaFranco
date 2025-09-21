<?php

session_start();
if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "alumno") {
    header("Location: login.html");
    exit;
}

?>

<h1>BIENVENIDO, <?php echo $_SESSION['nombre']; ?> <?php echo $_SESSION['apellido']; ?></h1>
<p>ESTE ES TU PANEL DE ALUMNO</p>
<a href="logout.php">CERRAR SESION</a>