<?php
if (!isset($conexion)) {
    require_once 'config/config.php';
}

echo '<div class="table-responsive">
    <table class="table table-hover" id="table_empleados">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Puesto</th>
                <th>Promedio</th>
                <th>Avatar</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>';

if (isset($_GET['estado']) && !empty($_GET['estado'])) {
    try {
        $query = "SELECT 
                    u.id_usuarios as id,
                    u.nombre,
                    u.apellido,
                    u.ocupacion as puesto,
                    u.avatar,
                    COALESCE(
                        (SELECT AVG(e.calificacion) 
                         FROM evaluaciones e 
                         WHERE e.id_usuario = u.id_usuarios), 
                        0
                    ) as promedio
                  FROM usuarios u
                  WHERE u.id_estado = :estado
                  AND u.estatus = 'activo'
                  ORDER BY u.nombre ASC";
        
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':estado', $_GET['estado'], PDO::PARAM_INT);
        $stmt->execute();
        
        while ($empleado = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr id="empleado_' . $empleado['id'] . '">
                <td>' . $empleado['id'] . '</td>
                <td>' . htmlspecialchars($empleado['nombre']) . '</td>
                <td>' . htmlspecialchars($empleado['apellido']) . '</td>
                <td>' . htmlspecialchars($empleado['puesto']) . '</td>
                <td>' . number_format($empleado['promedio'], 1) . '</td>
                <td>
                    <img src="acciones/fotos_empleados/' . $empleado['avatar'] . '" 
                        alt="Avatar de ' . htmlspecialchars($empleado['nombre']) . '"
                        class="rounded-circle"
                        width="50" height="50">
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="visualizar.php?id=' . $empleado['id'] . '" 
                        class="btn btn-success btn-sm me-1" title="Ver detalles">
                            <i class="bi bi-binoculars"></i>
                        </a>
                        <a href="#" 
                        class="btn btn-warning btn-sm me-1 btn-editar" 
                        title="Editar"
                        onclick="editarEmpleado(' . $empleado['id'] . '); return false;">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <button onclick="eliminarEmpleado(' . $empleado['id'] . ')" 
                                class="btn btn-danger btn-sm me-1" 
                                title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button type="button" 
                                class="btn btn-info btn-sm" 
                                onclick="abrirModalCalificaciones(' . $empleado['id'] . ', \'' . htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido'], ENT_QUOTES) . '\')" 
                                title="Calificaciones">
                            <i class="bi bi-star"></i>
                        </button>
                    </div>
                </td>
            </tr>';
        }
    } catch(PDOException $e) {
        echo '<tr><td colspan="7" class="text-danger">Error al cargar los empleados: ' . $e->getMessage() . '</td></tr>';
    }
}

echo '</tbody>
    </table>
</div>';