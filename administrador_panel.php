<?php

if (!isset($_SESSION["id_usaurio"]) || $_SESSION["rol"] != "administrador") {
    header("Location: index.html");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>PANEL DE ADMINISTRADOR</title>
</head>
<body>
    <h1>
        Bienvenido, 
        <h1><?php echo htmlspecialchars($_SESSION["nombre"]); ?>
        <?php echo htmlspecialchars($_SESSION["apellido"]); ?></h1>
        <h2>ESTE ES TU PANEL DE ADMINISTRADOR</h2>

        <ul>
            <li><a href="usuarios.php">GESTIONAR USUARIOS</a></li>
            <li><a href="asignaturas.php">GESTIONAR ASIGNATURAS</a></li>
            <li><a href="reportes.php">VER REPORTES</a></li>
        </ul>

        <a href="logout.php">CERRAR SESION</a>
    </h1>
</body>
</html>