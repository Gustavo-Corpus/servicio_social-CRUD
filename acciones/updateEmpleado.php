<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $avatarNombre = null;
        
        // Manejar la subida del avatar si se proporcionó uno nuevo
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatarNombre = uniqid() . '.' . $extension;
            $rutaDestino = __DIR__ . '/fotos_empleados/' . $avatarNombre;
            
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $rutaDestino)) {
                throw new Exception('Error al subir el archivo');
            }
        }

        if (!empty($_POST['id'])) {
            // Actualizar empleado existente
            if ($avatarNombre) {
                // Si hay nuevo avatar, obtener y eliminar el anterior
                $query = "SELECT avatar FROM usuarios WHERE id_usuarios = :id";
                $stmt = $conexion->prepare($query);
                $stmt->bindParam(':id', $_POST['id']);
                $stmt->execute();
                $oldAvatar = $stmt->fetchColumn();
                
                if ($oldAvatar) {
                    $oldAvatarPath = __DIR__ . '/fotos_empleados/' . $oldAvatar;
                    if (file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }

                $query = "UPDATE usuarios SET 
                            nombre = :nombre,
                            apellido = :apellido,
                            edad = :edad,
                            sexo = :sexo,
                            correo = :correo,
                            ocupacion = :ocupacion,
                            id_estado = :estado,
                            id_departamento = :departamento,
                            avatar = :avatar
                         WHERE id_usuarios = :id";
            } else {
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
            }
            
            $stmt = $conexion->prepare($query);
            $params = [
                ':nombre' => $_POST['nombre'],
                ':apellido' => $_POST['apellido'],
                ':edad' => $_POST['edad'],
                ':sexo' => $_POST['sexo'],
                ':correo' => $_POST['correo'],
                ':ocupacion' => $_POST['ocupacion'],
                ':estado' => $_POST['estado_empleado'],
                ':departamento' => $_POST['departamento'],
                ':id' => $_POST['id']
            ];
            
            if ($avatarNombre) {
                $params[':avatar'] = $avatarNombre;
            }
            
            $stmt->execute($params);
            
        } else {
            // Insertar nuevo empleado
            $query = "INSERT INTO usuarios 
                        (nombre, apellido, edad, sexo, correo, ocupacion, 
                         id_estado, id_departamento, estatus, avatar) 
                     VALUES 
                        (:nombre, :apellido, :edad, :sexo, :correo, :ocupacion,
                         :estado, :departamento, 'activo', :avatar)";
            
            $stmt = $conexion->prepare($query);
            $stmt->execute([
                ':nombre' => $_POST['nombre'],
                ':apellido' => $_POST['apellido'],
                ':edad' => $_POST['edad'],
                ':sexo' => $_POST['sexo'],
                ':correo' => $_POST['correo'],
                ':ocupacion' => $_POST['ocupacion'],
                ':estado' => $_POST['estado_empleado'],
                ':departamento' => $_POST['departamento'],
                ':avatar' => $avatarNombre
            ]);
        }
        
        echo json_encode(['success' => true]);
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>