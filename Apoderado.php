<?php
require_once 'includes/funciones.php';
$conn = get_db_connection();

$edit_mode = false;
$apoderado_a_editar = null;

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
        $error = "Error al actualizar: " . $e->getMessage();
    }
}

// --- Cargar datos para Edición ---
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $idPersonaApoderado = $_GET['edit'];
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
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1400px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .back-button, .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
        .back-button { background-color: #6c757d; }
        .btn-primary { background-color: #007bff; }
        .action-link { color: #007bff; text-decoration: none; }
        .edit-form { background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; }
        .form-grid div { display: flex; flex-direction: column; }
        .form-grid label { margin-bottom: 5px; font-weight: bold; }
        .form-grid input, .form-grid select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
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
</body>
</html>
