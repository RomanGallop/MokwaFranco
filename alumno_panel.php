<?php

session_start();
if (!isset($_SESSION["id_usuario"]) || $_SESSION["rol"] != "alumno") {
    header("Location: inicio_sesion.html");
    exit;
}

?>