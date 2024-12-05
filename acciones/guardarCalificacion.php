<?php

require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!empty($_POST['id'])) {
            // Actualizar calificación existente
            $query = "UPDATE evaluaciones 
                     SET mes = :mes,
                         anio = :anio,
                         calificacion = :calificacion,
                         comentarios = :comentarios
                     WHERE id_evaluacion = :id";
            
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(':id', $_POST['id']);
        } else {
            // Insertar nueva calificación
            $query = "INSERT INTO evaluaciones 
                        (id_usuario, mes, anio, calificacion, comentarios) 
                     VALUES 
                        (:empleado_id, :mes, :anio, :calificacion, :comentarios)";
            
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(':empleado_id', $_POST['empleado_id']);
        }

        // Parámetros comunes
        $stmt->bindParam(':mes', $_POST['mes']);
        $stmt->bindParam(':anio', $_POST['anio']);
        $stmt->bindParam(':calificacion', $_POST['calificacion']);
        $stmt->bindParam(':comentarios', $_POST['comentarios']);
        
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}