<?php
require_once 'includes/funciones.php';
$conn = get_db_connection();

$edit_mode = false;
$apoderado_a_editar = null;
$trigger_error = '';

// --- Lógica de Actualización ---
if (isset($_POST['update'])) {
    $idPersona = $_POST['idPersona'];
    $nombres = $_POST['Nombres'];
    $apellido_paterno = $_POST['Apellido_Paterno'];
    $apellido_materno = $_POST['Apellido_Materno'];
    $dni = $_POST['DNI'];
    $direccion = $_POST['Direccion'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $gradoInstruccion = $_POST['gradoInstruccion'];
    $ocupacion = $_POST['Ocupacion'];
    $parentesco = $_POST['Parentesco'];
    $viveConEstudiante = $_POST['viveConEstudiante'];

    //UPDATES
    $conn->begin_transaction();
    try {
        $sql_persona = "UPDATE persona SET Nombres=?, Apellido_Paterno=?, Apellido_Materno=?, DNI=?, Direccion=?, telefono=? WHERE idPersona=?";
        $stmt_persona = $conn->prepare($sql_persona);
        $stmt_persona->bind_param("ssssssi", $nombres, $apellido_paterno, $apellido_materno, $dni, $direccion, $telefono, $idPersona);
        $stmt_persona->execute();

        $sql_no_estudiante = "UPDATE No_Estudiante SET correo=?, gradoInstruccion=? WHERE idPersona=?";
        $stmt_no_estudiante = $conn->prepare($sql_no_estudiante);
        $stmt_no_estudiante->bind_param("ssi", $correo, $gradoInstruccion, $idPersona);
        $stmt_no_estudiante->execute();

        $sql_ppff = "UPDATE PPFF SET Ocupacion=? WHERE idPersona=?";
        $stmt_ppff = $conn->prepare($sql_ppff);
        $stmt_ppff->bind_param("si", $ocupacion, $idPersona);
        $stmt_ppff->execute();

        $sql_apoderado = "UPDATE apoderado SET Parentesco=?, viveConEstudiante=? WHERE idPersonaPPFF=?";
        $stmt_apoderado = $conn->prepare($sql_apoderado);
        $stmt_apoderado->bind_param("ssi", $parentesco, $viveConEstudiante, $idPersona);
        $stmt_apoderado->execute();

        $conn->commit();
        header("Location: Apoderado.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        // TRIGGER de Horario
        if (strpos($e->getMessage(), 'control_horario_persona') !== false) {
            $trigger_error = $e->getMessage();
        } else {
            $error = "Error al actualizar: " . $e->getMessage();
        }
    }
}

// --- Cargar datos para Edición ---
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $idPersonaApoderado = $_GET['edit'];
    // SELECT para la pantalla de edicion
    $sql_edit = "SELECT p.*, ne.correo, ne.gradoInstruccion, ppff.Ocupacion, a.Parentesco, a.viveConEstudiante 
                 FROM persona p
                 JOIN No_Estudiante ne ON p.idPersona = ne.idPersona
                 JOIN PPFF ppff ON p.idPersona = ppff.idPersona
                 JOIN apoderado a ON p.idPersona = a.idPersonaPPFF
                 WHERE p.idPersona = ?";
    $stmt = $conn->prepare($sql_edit);
    $stmt->bind_param("i", $idPersonaApoderado);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $apoderado_a_editar = $result->fetch_assoc();
    }
    $stmt->close();
}

// --- Obtener lista de Apoderados ---
$apoderados = [];
// SELECT DE TABLA APODERADO
$sql = "SELECT 
            p_apoderado.idPersona AS idPersonaApoderado,
            p_apoderado.Nombres AS NombresApoderado,
            p_apoderado.Apellido_Paterno AS ApellidoPaternoApoderado,
            p_apoderado.Apellido_Materno AS ApellidoMaternoApoderado,
            p_apoderado.DNI AS DNIApoderado,
            p_apoderado.Direccion AS DireccionApoderado,
            p_apoderado.telefono AS TelefonoApoderado,
            ne.correo AS CorreoApoderado,
            ne.gradoInstruccion,
            ppff.Ocupacion,
            ppff.Vive AS ViveApoderado,
            apo.Parentesco,
            apo.viveConEstudiante,
            p_estudiante.Nombres AS NombresEstudiante,
            p_estudiante.Apellido_Paterno AS ApellidoPaternoEstudiante,
            est.codAlumno
        FROM apoderado apo
        JOIN PPFF ppff ON apo.idPersonaPPFF = ppff.idPersona
        JOIN No_Estudiante ne ON ppff.idPersona = ne.idPersona
        JOIN persona p_apoderado ON ne.idPersona = p_apoderado.idPersona
        JOIN estudiante est ON apo.idPersonaEst = est.idPersona
        JOIN persona p_estudiante ON est.idPersona = p_estudiante.idPersona
        ORDER BY ApellidoPaternoApoderado, NombresApoderado";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $apoderados[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Apoderados</title>
    <link rel="stylesheet" href="css/apoderado_style.css">
</head>
<body>
    <div class="container">
        <h1>Lista de Apoderados</h1>

        <?php if ($edit_mode && $apoderado_a_editar): ?>
        <div class="edit-form">
            <h2>Editando Apoderado: <?= htmlspecialchars($apoderado_a_editar['Nombres'] . ' ' . $apoderado_a_editar['Apellido_Paterno']) ?></h2>
            <form method="POST" action="Apoderado.php">
                <input type="hidden" name="idPersona" value="<?= $apoderado_a_editar['idPersona'] ?>">
                <div class="form-grid">
                    <div><label>Nombres:</label><input type="text" name="Nombres" value="<?= htmlspecialchars($apoderado_a_editar['Nombres']) ?>"></div>
                    <div><label>Apellido Paterno:</label><input type="text" name="Apellido_Paterno" value="<?= htmlspecialchars($apoderado_a_editar['Apellido_Paterno']) ?>"></div>
                    <div><label>Apellido Materno:</label><input type="text" name="Apellido_Materno" value="<?= htmlspecialchars($apoderado_a_editar['Apellido_Materno']) ?>"></div>
                    <div><label>DNI:</label><input type="text" name="DNI" value="<?= htmlspecialchars($apoderado_a_editar['DNI']) ?>"></div>
                    <div><label>Dirección:</label><input type="text" name="Direccion" value="<?= htmlspecialchars($apoderado_a_editar['Direccion']) ?>"></div>
                    <div><label>Teléfono:</label><input type="text" name="telefono" value="<?= htmlspecialchars($apoderado_a_editar['telefono']) ?>"></div>
                    <div><label>Correo:</label><input type="email" name="correo" value="<?= htmlspecialchars($apoderado_a_editar['correo']) ?>"></div>
                    <div><label>Grado de Instrucción:</label><input type="text" name="gradoInstruccion" value="<?= htmlspecialchars($apoderado_a_editar['gradoInstruccion']) ?>"></div>
                    <div><label>Ocupación:</label><input type="text" name="Ocupacion" value="<?= htmlspecialchars($apoderado_a_editar['Ocupacion']) ?>"></div>
                    <div><label>Parentesco:</label><input type="text" name="Parentesco" value="<?= htmlspecialchars($apoderado_a_editar['Parentesco']) ?>"></div>
                    <div>
                        <label>¿Vive con el estudiante?</label>
                        <select name="viveConEstudiante">
                            <option value="Si" <?= $apoderado_a_editar['viveConEstudiante'] == 'Si' ? 'selected' : '' ?>>Sí</option>
                            <option value="No" <?= $apoderado_a_editar['viveConEstudiante'] == 'No' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="update" class="btn btn-primary">Actualizar Apoderado</button>
                <a href="Apoderado.php" class="back-button">Cancelar</a>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if (empty($apoderados)): ?>
            <p>No hay apoderados registrados.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Apoderado</th>
                        <th>DNI</th>
                        <th>Parentesco</th>
                        <th>Ocupación</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Estudiante a Cargo</th>
                        <th>Código Alumno</th>
                        <th>¿Vive con Estudiante?</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apoderados as $apoderado): ?>
                        <tr>
                            <td><?= htmlspecialchars($apoderado['ApellidoPaternoApoderado'] . ' ' . $apoderado['ApellidoMaternoApoderado'] . ', ' . $apoderado['NombresApoderado']) ?></td>
                            <td><?= htmlspecialchars($apoderado['DNIApoderado']) ?></td>
                            <td><?= htmlspecialchars($apoderado['Parentesco']) ?></td>
                            <td><?= htmlspecialchars($apoderado['Ocupacion']) ?></td>
                            <td><?= htmlspecialchars($apoderado['DireccionApoderado']) ?></td>
                            <td><?= htmlspecialchars($apoderado['TelefonoApoderado']) ?></td>
                            <td><?= htmlspecialchars($apoderado['CorreoApoderado']) ?></td>
                            <td><?= htmlspecialchars($apoderado['ApellidoPaternoEstudiante'] . ', ' . $apoderado['NombresEstudiante']) ?></td>
                            <td><?= htmlspecialchars($apoderado['codAlumno']) ?></td>
                            <td><?= htmlspecialchars($apoderado['viveConEstudiante']) ?></td>
                            <td>
                                <a href="Apoderado.php?edit=<?= $apoderado['idPersonaApoderado'] ?>" class="action-link">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <a href="index.php" class="back-button">Volver al Menú Principal</a>
    </div>
    <script>
        <?php if (!empty($trigger_error)): ?>
        alert(<?= json_encode($trigger_error) ?>);
        <?php endif; ?>
    </script>
</body>
</html>
