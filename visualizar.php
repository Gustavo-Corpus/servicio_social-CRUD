<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalles del Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <h1 class="text-center">
                    <a href="./" class="float-start">
                        <i class="bi bi-arrow-left-circle"></i>
                    </a>
                    Datos del empleado
                    <hr>
                </h1>
                <?php
                if (isset($_GET['id'])) {
                    include("config/config.php");
                    
                    $query = "SELECT 
                                u.*,
                                d.nombre_departamento,
                                e.estado,
                                (SELECT AVG(calificacion) 
                                 FROM evaluaciones ev 
                                 WHERE ev.id_usuario = u.id_usuarios 
                                 AND anio = YEAR(CURRENT_DATE)) as promedio
                             FROM usuarios u
                             LEFT JOIN departamentos d ON u.id_departamento = d.id_departamento
                             LEFT JOIN estados e ON u.id_estado = e.id_estado
                             WHERE u.id_usuarios = :id";
                    
                    $stmt = $conexion->prepare($query);
                    $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$empleado) {
                        header("location:./");
                        exit;
                    }
                ?>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Nombre completo: <strong><?php echo $empleado['nombre'] . ' ' . $empleado['apellido']; ?></strong></li>
                        <li class="list-group-item">Edad: <strong><?php echo $empleado['edad']; ?></strong></li>
                        <li class="list-group-item">Correo: <strong><?php echo $empleado['correo']; ?></strong></li>
                        <li class="list-group-item">Puesto: <strong><?php echo $empleado['ocupacion']; ?></strong></li>
                        <li class="list-group-item">Departamento: <strong><?php echo $empleado['nombre_departamento']; ?></strong></li>
                        <li class="list-group-item">Estado: <strong><?php echo $empleado['estado']; ?></strong></li>
                        <li class="list-group-item">Promedio de calificación: <strong><?php echo number_format($empleado['promedio'], 1); ?></strong></li>
                    </ul>
                <?php
                } else {
                    header("location:./");
                    exit;
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>