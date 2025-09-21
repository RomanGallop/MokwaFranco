<?php

session_start();
if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "profesor") {
    header("Location: login.html");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>PANEL DE PROFESOR</title>
</head>
<body>
    <h1>BIENVENIDO, <?php echo $_SESSION['nombre']; ?> <?php echo $_SESSION['apellido']; ?></h1>
    <p>ESTE ES TU PANEL DE PROFESOR.</p>

    <ul>
        <li><a href="mis_asignaturas.php">VER MIS ASIGNATURAS</a></li>
        <li><a href="ingresar_notas.php">INGRESAR NOTAS</a></li>
        <li><a href="asistencia_profesor.php">REGISTRAR ASISTENCIA</a></li>
    </ul>

    <a href="logout.php">CERRAR SESION</a>
</body>
</html>