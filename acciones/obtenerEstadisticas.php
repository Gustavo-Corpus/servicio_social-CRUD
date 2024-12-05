<?php
require_once '../config/config.php';

try {
    // Obtener estadísticas generales
    $stats = [
        'totalEmpleados' => 0,
        'promedioGlobal' => 0,
        'totalEstados' => 0,
        'distribucionEstados' => [],
        'promediosPorEstado' => []
    ];

    // Total empleados activos
    $query = "SELECT COUNT(*) as total FROM usuarios WHERE estatus = 'activo'";
    $result = $conexion->query($query)->fetch(PDO::FETCH_ASSOC);
    $stats['totalEmpleados'] = $result['total'];

    // Promedio global de calificaciones
    $query = "SELECT AVG(calificacion) as promedio FROM evaluaciones";
    $result = $conexion->query($query)->fetch(PDO::FETCH_ASSOC);
    $stats['promedioGlobal'] = round($result['promedio'], 2);

    // Total de estados con empleados
    $query = "SELECT COUNT(DISTINCT id_estado) as total FROM usuarios WHERE estatus = 'activo'";
    $result = $conexion->query($query)->fetch(PDO::FETCH_ASSOC);
    $stats['totalEstados'] = $result['total'];

    // Distribución de empleados por estado
    $query = "SELECT e.estado, COUNT(u.id_usuarios) as total
              FROM usuarios u
              JOIN estados e ON u.id_estado = e.id_estado
              WHERE u.estatus = 'activo'
              GROUP BY e.estado
              ORDER BY total DESC";
    $stats['distribucionEstados'] = $conexion->query($query)->fetchAll(PDO::FETCH_ASSOC);

    // Promedio de calificaciones por estado
    $query = "SELECT e.estado,
                     COALESCE(AVG(ev.calificacion), 0) as promedio
              FROM estados e
              LEFT JOIN usuarios u ON e.id_estado = u.id_estado
              LEFT JOIN evaluaciones ev ON u.id_usuarios = ev.id_usuario
              WHERE u.estatus = 'activo'
              GROUP BY e.estado
              ORDER BY promedio DESC";
    $stats['promediosPorEstado'] = $conexion->query($query)->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stats);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>