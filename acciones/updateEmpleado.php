<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $isUpdate = isset($_POST['id']) && !empty($_POST['id']);
        
        if ($isUpdate) {
            // Actualizar empleado existente
            $query = "UPDATE usuarios SET 
                        nombre = :nombre,
                        apellido = :apellido,
                        edad = :edad,
                        sexo = :sexo,
                        correo = :correo,
                        ocupacion = :ocupacion,
                        id_estado = :estado,
                        id_departamento = :departamento
                     WHERE id_usuarios = :id";
            
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(':id', $_POST['id']);
        } else {
            // Insertar nuevo empleado
            $query = "INSERT INTO usuarios 
                        (nombre, apellido, edad, sexo, correo, ocupacion, 
                         id_estado, id_departamento, estatus) 
                     VALUES 
                        (:nombre, :apellido, :edad, :sexo, :correo, :ocupacion,
                         :estado, :departamento, 'activo')";
            
            $stmt = $conexion->prepare($query);
        }

        // Bind parámetros comunes
        $stmt->bindParam(':nombre', $_POST['nombre']);
        $stmt->bindParam(':apellido', $_POST['apellido']);
        $stmt->bindParam(':edad', $_POST['edad']);
        $stmt->bindParam(':sexo', $_POST['sexo']);
        $stmt->bindParam(':correo', $_POST['correo']);
        $stmt->bindParam(':ocupacion', $_POST['ocupacion']);
        $stmt->bindParam(':estado', $_POST['estado_empleado']);
        $stmt->bindParam(':departamento', $_POST['departamento']);
        
        if ($stmt->execute()) {
            header('Location: ../index.php?success=true');
            exit;
        } else {
            header('Location: ../index.php?error=true');
            exit;
        }
    } catch(PDOException $e) {
        header('Location: ../index.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}
?>