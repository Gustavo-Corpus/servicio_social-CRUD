<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_de_datos = "servicio_social";

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$base_de_datos;charset=utf8",
        $usuario,
        $contrasena
    );
    
    // Configura PDO para que lance excepciones en caso de errores
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configura el modo de obtención por defecto como array asociativo
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>