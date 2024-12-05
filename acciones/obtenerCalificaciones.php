<?php
require_once '../config/config.php';

if (isset($_GET['empleado_id'])) {
    try {
        $query = "SELECT * FROM evaluaciones 
                 WHERE id_usuario = :empleado_id 
                 ORDER BY anio DESC, mes DESC";
        
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':empleado_id', $_GET['empleado_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        $calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($calificaciones);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}