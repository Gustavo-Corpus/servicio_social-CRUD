<?php
// acciones/exportar.php
require_once '../config/config.php';

if (isset($_GET['estado'])) {
    try {
        // Obtener el nombre del estado para el nombre del archivo
        $queryEstado = "SELECT estado FROM estados WHERE id_estado = :id_estado";
        $stmtEstado = $conexion->prepare($queryEstado);
        $stmtEstado->bindParam(':id_estado', $_GET['estado']);
        $stmtEstado->execute();
        $nombreEstado = $stmtEstado->fetchColumn();

        // Consulta para obtener los empleados del estado
        $query = "SELECT 
                    u.id_usuarios as ID,
                    u.nombre as Nombre,
                    u.apellido as Apellido,
                    u.edad as Edad,
                    u.sexo as Sexo,
                    u.correo as Correo,
                    u.ocupacion as Puesto,
                    d.nombre_departamento as Departamento,
                    COALESCE((SELECT AVG(calificacion) 
                             FROM evaluaciones e 
                             WHERE e.id_usuario = u.id_usuarios), 0) as Promedio
                 FROM usuarios u
                 LEFT JOIN departamentos d ON u.id_departamento = d.id_departamento
                 WHERE u.id_estado = :estado
                 AND u.estatus = 'activo'
                 ORDER BY u.nombre ASC";

        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':estado', $_GET['estado']);
        $stmt->execute();
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Configurar headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Empleados_' . str_replace(' ', '_', $nombreEstado) . '_' . date('Y-m-d') . '.csv');

        // Crear el archivo CSV
        $output = fopen('php://output', 'w');
        
        // Agregar BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Escribir encabezados
        fputcsv($output, array_keys($empleados[0]));

        // Escribir datos
        foreach ($empleados as $empleado) {
            // Redondear el promedio a 1 decimal
            $empleado['Promedio'] = number_format($empleado['Promedio'], 1);
            fputcsv($output, $empleado);
        }

        fclose($output);
        exit;

    } catch(PDOException $e) {
        echo "Error al exportar: " . $e->getMessage();
        exit;
    }
} else {
    echo "Por favor seleccione un estado primero";
    exit;
}