<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_de_datos = "ss_crud";

// Crear conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configuración para manejar caracteres especiales
$conexion->set_charset("utf8mb4");
?>