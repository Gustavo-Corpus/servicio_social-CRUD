<?php
require_once '../config/config.php';

if (isset($_GET['id'])) {
    try {
        $query = "SELECT 
                    u.*, 
                    d.nombre_departamento,
                    e.estado
                 FROM usuarios u
                 LEFT JOIN departamentos d ON u.id_departamento = d.id_departamento
                 LEFT JOIN estados e ON u.id_estado = e.id_estado
                 WHERE u.id_usuarios = :id
                 AND u.estatus = 'activo'";
        
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
        $stmt->execute();
        
        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($empleado) {
            echo json_encode($empleado);
        } else {
            echo json_encode(['error' => 'Empleado no encontrado']);
        }
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID no proporcionado']);
}