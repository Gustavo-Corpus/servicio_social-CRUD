<?php
// formulario.php
?>
<form id="empleadoForm" method="POST" action="acciones/updateEmpleado.php" enctype="multipart/form-data">
    <?php if ($empleadoEdit) { ?>
        <input type="hidden" name="id" value="<?php echo $empleadoEdit['id_usuarios']; ?>">
    <?php } ?>

    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" required 
               value="<?php echo $empleadoEdit ? $empleadoEdit['nombre'] : ''; ?>">
    </div>

    <div class="mb-3">
        <label for="apellido" class="form-label">Apellido</label>
        <input type="text" class="form-control" id="apellido" name="apellido" required 
               value="<?php echo $empleadoEdit ? $empleadoEdit['apellido'] : ''; ?>">
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="edad" class="form-label">Edad</label>
            <input type="number" class="form-control" id="edad" name="edad" required 
                   value="<?php echo $empleadoEdit ? $empleadoEdit['edad'] : ''; ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Sexo</label>
            <div class="mt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sexo" id="sexoM" 
                           value="Masculino" <?php echo ($empleadoEdit && $empleadoEdit['sexo'] == 'Masculino') ? 'checked' : ''; ?> required>
                    <label class="form-check-label" for="sexoM">Masculino</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sexo" id="sexoF" 
                           value="Femenino" <?php echo ($empleadoEdit && $empleadoEdit['sexo'] == 'Femenino') ? 'checked' : ''; ?> required>
                    <label class="form-check-label" for="sexoF">Femenino</label>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="correo" class="form-label">Correo</label>
        <input type="email" class="form-control" id="correo" name="correo" required 
               value="<?php echo $empleadoEdit ? $empleadoEdit['correo'] : ''; ?>">
    </div>

    <div class="mb-3">
        <label for="estado_empleado" class="form-label">Estado</label>
        <select class="form-select" id="estado_empleado" name="estado_empleado" required>
            <option value="">Seleccione...</option>
            <?php
            try {
                $query = "SELECT id_estado, estado FROM estados ORDER BY estado";
                $stmt = $conexion->prepare($query);
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    $selected = ($empleadoEdit && $empleadoEdit['id_estado'] == $row['id_estado']) ? 'selected' : '';
                    echo "<option value='" . $row['id_estado'] . "' " . $selected . ">" . $row['estado'] . "</option>";
                }
            } catch(PDOException $e) {
                echo "<option value=''>Error al cargar estados</option>";
            }
            ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="departamento" class="form-label">Departamento</label>
        <select class="form-select" id="departamento" name="departamento" required>
            <option value="">Seleccione...</option>
            <?php
            try {
                $query = "SELECT id_departamento, nombre_departamento FROM departamentos ORDER BY nombre_departamento";
                $stmt = $conexion->prepare($query);
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                    $selected = ($empleadoEdit && $empleadoEdit['id_departamento'] == $row['id_departamento']) ? 'selected' : '';
                    echo "<option value='" . $row['id_departamento'] . "' " . $selected . ">" . $row['nombre_departamento'] . "</option>";
                }
            } catch(PDOException $e) {
                echo "<option value=''>Error al cargar departamentos</option>";
            }
            ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="ocupacion" class="form-label">Puesto</label>
        <input type="text" class="form-control" id="ocupacion" name="ocupacion" required 
               value="<?php echo $empleadoEdit ? $empleadoEdit['ocupacion'] : ''; ?>">
    </div>

    <div class="mb-3">
        <label for="avatar" class="form-label">Foto del empleado</label>
        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
        <?php if ($empleadoEdit && isset($empleadoEdit['avatar']) && $empleadoEdit['avatar']): ?>
            <div class="mt-2">
                <img src="acciones/fotos_empleados/<?php echo $empleadoEdit['avatar']; ?>" 
                     alt="Avatar actual" 
                     class="rounded-circle"
                     width="50" height="50">
                <small class="text-muted ms-2">Avatar actual</small>
            </div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <?php echo $empleadoEdit ? 'Actualizar empleado' : 'Agregar empleado'; ?>
    </button>
</form>