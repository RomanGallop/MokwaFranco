<?php

$host = "127.0.0.1";
$usuario = "root";
$password = "";
$bd = "proyecto1";

$conn = new mysqli($host, $usuario, $password, $bd);

if ($conn->connect_error) {
    die("FALLO DE CONEXION" . $conn->connect_error);
}

?>