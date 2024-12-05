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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <button class="btn btn-primary me-2" onclick="mostrarEstadisticas()" title="Ver Estadísticas">
                            <i class="bi bi-bar-chart-fill"></i>
                        </button>
                        <button class="btn btn-success me-2" onclick="exportarEmpleados()" title="Exportar a CSV">
                            <i class="bi bi-filetype-csv"></i>
                        </button>
                    </span>
                </h2>
                <hr>
                <div id="empleadosContainer">
                    <?php include("empleados.php"); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCalificaciones" tabindex="-1" aria-labelledby="modalCalificacionesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCalificacionesLabel">Calificaciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulario de calificación -->
                    <form id="calificacionForm">
                        <input type="hidden" id="empleado_id" name="empleado_id">
                        <input type="hidden" id="calificacion_id" name="calificacion_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="mes" class="form-label">Mes</label>
                                <select class="form-select" id="mes" name="mes" required>
                                    <option value="1">Enero</option>
                                    <option value="2">Febrero</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Abril</option>
                                    <option value="5">Mayo</option>
                                    <option value="6">Junio</option>
                                    <option value="7">Julio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="anio" class="form-label">Año</label>
                                <select class="form-select" id="anio" name="anio" required>
                                    <?php 
                                    $currentYear = date('Y');
                                    for($i = $currentYear; $i >= $currentYear - 5; $i--) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="calificacion" class="form-label">Calificación (0-10)</label>
                            <input type="number" class="form-control" id="calificacion" name="calificacion" min="0" max="10" step="0.1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comentarios" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="comentarios" name="comentarios" rows="3"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="limpiarFormularioCalificacion()">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Calificación</button>
                        </div>
                    </form>

                    <hr>

                    <!-- Tabla de calificaciones existentes -->
                    <h5 class="mt-4">Evaluaciones existentes</h5>
                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaCalificaciones">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th>Año</th>
                                    <th>Calificación</th>
                                    <th>Comentarios</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="calificacionesBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEstadisticas" tabindex="-1" aria-labelledby="modalEstadisticasLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEstadisticasLabel">Estadísticas Generales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Métricas generales -->
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Empleados</h5>
                                    <h2 id="totalEmpleados">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Calificación Promedio</h5>
                                    <h2 id="promedioGlobal">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Total Estados</h5>
                                    <h2 id="totalEstados">0</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficas -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Distribución por Estado</h5>
                            <canvas id="pieChart"></canvas>
                        </div>
                        <div class="col-md-6">
                            <h5>Promedio de Calificaciones por Estado</h5>
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
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