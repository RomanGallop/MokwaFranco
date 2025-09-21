<?php

session_start();
require "index.php";

# recibir datos del formulario
$email = $_POST["email"];
$clave = $_POST["clave"];

# buscar usuario en la base de datos
$sql = "SELECT u.id_usuario, u.clave, u.rol, p.nombre, p.apellido
        FROM usuario u
        JOIN persona p ON u.id_persona = p.id_persona
        WHERE u.email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();

    # verificar clave
    if (password_verify($clave, $usuario["clave"])) {
        # guardar datos en sesion
        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["rol"] = $usuario["rol"];
        $_SESSION["nombre"] = $usuario["nombre"];
        $_SESSION["apellido"] = $usuario["apellido"];

        # redirigir segun rol
        if ($usuario["rol"] === "alumno") {
            header("Location: alumno_panel.php");
        } else if ($usuario["rol"] === "profesor") {
            header("Location: profesor_panel.php");
        } else {
            header("Location: administrador_panel.php");
        }
        exit;
    } else {
        echo "CLAVE INCORRECTA";
    }
} else {
    echo "USUARIO NO ENCONTRADO";
}

?>