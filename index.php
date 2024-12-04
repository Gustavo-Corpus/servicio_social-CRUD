<!-- index.php -->
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Empleados por Estado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.1/css/dataTables.bootstrap5.css">
</head>

<body>
    <?php
    include("config/config.php");
    include("acciones/acciones.php");
    ?>
    <h1 class="text-center mt-5 mb-5 fw-bold">Sistema de Empleados por Estado</h1>

    <div class="container">
        <div class="row justify-content-md-center">
            <!-- Formulario a la izquierda -->
            <div class="col-md-4" style="border-right: 1px solid #dee2e6;">
                <?php
                $empleadoEdit = null;
                if (isset($_GET['id'])) {
                    $id = $_GET['id'];
                    try {
                        $query = "SELECT u.*, d.nombre_departamento, e.estado 
                                 FROM usuarios u
                                 LEFT JOIN departamentos d ON u.id_departamento = d.id_departamento
                                 LEFT JOIN estados e ON u.id_estado = e.id_estado
                                 WHERE u.id_usuarios = :id";
                        $stmt = $conexion->prepare($query);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        $empleadoEdit = $stmt->fetch(PDO::FETCH_ASSOC);
                    } catch(PDOException $e) {
                        echo "Error al obtener datos del empleado: " . $e->getMessage();
                    }
                }
                include("formulario.php");
                ?>
            </div>

            <!-- Lista de empleados a la derecha -->
            <div class="col-md-8">
                <div class="mb-4">
                    <label for="estado" class="form-label">Seleccione un estado:</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Seleccione un estado...</option>
                        <?php
                        try {
                            $query = "SELECT id_estado, estado FROM estados ORDER BY estado";
                            $stmt = $conexion->prepare($query);
                            $stmt->execute();
                            while ($row = $stmt->fetch()) {
                                echo "<option value='" . $row['id_estado'] . "'>" . $row['estado'] . "</option>";
                            }
                        } catch(PDOException $e) {
                            echo "<option value=''>Error al cargar estados: " . $e->getMessage() . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <h2 class="text-center">Lista de Empleados
                    <span class="float-end">
                        <a href="acciones/exportar.php" class="btn btn-success" title="Exportar a CSV" download="empleados.csv">
                            <i class="bi bi-filetype-csv"></i>
                        </a>
                    </span>
                </h2>
                <hr>
                <div id="empleadosContainer">
                    <?php include("empleados.php"); ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.1/js/dataTables.bootstrap5.js"></script>
    <script src="assets/js/home.js"></script>
</body>
</html>