<?php
require_once 'includes/funciones.php';
$conn = get_db_connection();

$edit_mode = false;
$nota_a_editar = null;

// 4. Procesar Actualización
if (isset($_POST['update'])) {
    $idRegNota = $_POST['idRegNota'];
    $nota = $_POST['Nota'];
    $fechaEvaluacion = $_POST['FechaEvaluacion'];
    $tipoEvaluacion = $_POST['TipoEvaluacion'];
    $bimestre = $_POST['Bimestre'];
    $idMatricula = $_POST['idMatricula'];
    $idImparte = $_POST['idImparte'];

    $sql = "UPDATE RegNotas SET Nota = ?, FechaEvaluacion = ?, TipoEvaluacion = ?, Bimestre = ?, idMatricula = ?, idImparte = ? WHERE idRegNota = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dssiiii", $nota, $fechaEvaluacion, $tipoEvaluacion, $bimestre, $idMatricula, $idImparte, $idRegNota);
    $stmt->execute();
    $stmt->close();

    header("Location: RegistroNotas.php");
    exit();
}

// 1. Insertar nueva nota
if (isset($_POST['add'])) {
    $nota = $_POST['Nota'];
    $fechaEvaluacion = $_POST['FechaEvaluacion'];
    $tipoEvaluacion = $_POST['TipoEvaluacion'];
    $bimestre = $_POST['Bimestre'];
    $idMatricula = $_POST['idMatricula'];
    $idImparte = $_POST['idImparte'];

    $sql = "INSERT INTO RegNotas (Nota, FechaEvaluacion, TipoEvaluacion, Bimestre, idMatricula, idImparte) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dssiii", $nota, $fechaEvaluacion, $tipoEvaluacion, $bimestre, $idMatricula, $idImparte);
    $stmt->execute();
    $stmt->close();

    header("Location: RegistroNotas.php");
    exit();
}

// 3. Cargar datos para edición
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $idRegNota = $_GET['edit'];
    $sql_edit = "SELECT * FROM RegNotas WHERE idRegNota = ?";
    $stmt = $conn->prepare($sql_edit);
    $stmt->bind_param("i", $idRegNota);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $nota_a_editar = $result->fetch_assoc();
    }
    $stmt->close();
}


// 2. Obtener notas registradas con nombres de estudiantes y cursos
$notas = [];
$sql_notas = "SELECT rn.idRegNota, rn.Nota, rn.FechaEvaluacion, rn.TipoEvaluacion, rn.Bimestre,
                     p_est.Nombres AS NombreEstudiante, p_est.Apellido_Paterno AS ApellidoEstudiante,
                     p_doc.Nombres AS NombreDocente, p_doc.Apellido_Paterno AS ApellidoDocente,
                     a.nombreArea AS NombreCurso
              FROM RegNotas rn
              JOIN matricula m ON rn.idMatricula = m.idMatricula
              JOIN persona p_est ON m.idPersona = p_est.idPersona
              JOIN DocenteImparteCurso dic ON rn.idImparte = dic.idImparte
              JOIN persona p_doc ON dic.idPersona = p_doc.idPersona
              JOIN curso c ON dic.idCurso = c.idCurso
              JOIN Area a ON c.idArea = a.idArea
              ORDER BY rn.FechaEvaluacion DESC, p_est.Apellido_Paterno";
$result = $conn->query($sql_notas);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Notas</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef1f5;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 30px;
        }
        .form-section, .table-section {
            flex: 1;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-grid input,
        .form-grid select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .form-submit-button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .form-submit-button:hover {
            background-color: #218838;
        }
        .update-button {
            background-color: #007bff;
        }
        .update-button:hover {
            background-color: #0069d9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.95em;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
            padding: 10px;
            color: #333;
        }
        td {
            padding: 8px;
            text-align: left;
        }
        .action-link {
            color: #007bff;
            text-decoration: none;
        }
        .action-link:hover {
            text-decoration: underline;
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h2><?= $edit_mode ? 'Editar Nota' : 'Registrar Nota' ?></h2>
            <form action="RegistroNotas.php" method="POST">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="idRegNota" value="<?= $nota_a_editar['idRegNota'] ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <input type="number" name="Nota" step="0.01" placeholder="Nota (ej. 15.50)" value="<?= htmlspecialchars($nota_a_editar['Nota'] ?? '') ?>" required>
                    <input type="date" name="FechaEvaluacion" value="<?= htmlspecialchars($nota_a_editar['FechaEvaluacion'] ?? '') ?>" required>
                    <input type="text" name="TipoEvaluacion" placeholder="Tipo de Evaluación (Parcial, Final...)" value="<?= htmlspecialchars($nota_a_editar['TipoEvaluacion'] ?? '') ?>" required>
                    <input type="number" name="Bimestre" min="1" max="4" placeholder="Bimestre" value="<?= htmlspecialchars($nota_a_editar['Bimestre'] ?? '') ?>" required>

                    <select name="idMatricula" required>
                        <option value="">Seleccione Estudiante</option>
                        <?php
                        $sql_matriculas = "SELECT m.idMatricula, p.Nombres, p.Apellido_Paterno, p.Apellido_Materno FROM matricula m JOIN persona p ON m.idPersona = p.idPersona ORDER BY p.Apellido_Paterno, p.Nombres";
                        $matriculas_result = $conn->query($sql_matriculas);
                        while ($row = $matriculas_result->fetch_assoc()) {
                            $nombre_completo = htmlspecialchars($row['Apellido_Paterno'] . ' ' . $row['Apellido_Materno'] . ', ' . $row['Nombres']);
                            $selected = ($edit_mode && $row['idMatricula'] == $nota_a_editar['idMatricula']) ? 'selected' : '';
                            echo "<option value='{$row['idMatricula']}' {$selected}>{$nombre_completo}</option>";
                        }
                        ?>
                    </select>

                    <select name="idImparte" required>
                        <option value="">Seleccione Docente/Curso</option>
                        <?php
                        $sql_impartes = "SELECT dic.idImparte, p.Nombres, p.Apellido_Paterno, a.nombreArea FROM DocenteImparteCurso dic JOIN persona p ON dic.idPersona = p.idPersona JOIN curso c ON dic.idCurso = c.idCurso JOIN Area a ON c.idArea = a.idArea ORDER BY p.Apellido_Paterno, a.nombreArea";
                        $impartes_result = $conn->query($sql_impartes);
                        while ($row = $impartes_result->fetch_assoc()) {
                            $display_text = htmlspecialchars($row['Apellido_Paterno'] . ' ' . $row['Nombres'] . ' - ' . $row['nombreArea']);
                            $selected = ($edit_mode && $row['idImparte'] == $nota_a_editar['idImparte']) ? 'selected' : '';
                            echo "<option value='{$row['idImparte']}' {$selected}>{$display_text}</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php if ($edit_mode): ?>
                    <button type="submit" name="update" class="form-submit-button update-button">Actualizar Nota</button>
                <?php else: ?>
                    <button type="submit" name="add" class="form-submit-button">Registrar Nota</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-section">
            <h2>Notas Registradas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Nota</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Bimestre</th>
                        <th>Docente / Curso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notas)): ?>
                        <tr><td colspan="7">No hay registros aún.</td></tr>
                    <?php else: ?>
                        <?php foreach ($notas as $nota): ?>
                            <tr>
                                <td><?= htmlspecialchars($nota['ApellidoEstudiante'] . ' ' . $nota['NombreEstudiante']) ?></td>
                                <td><?= htmlspecialchars($nota['Nota']) ?></td>
                                <td><?= htmlspecialchars($nota['FechaEvaluacion']) ?></td>
                                <td><?= htmlspecialchars($nota['TipoEvaluacion']) ?></td>
                                <td><?= htmlspecialchars($nota['Bimestre']) ?></td>
                                <td><?= htmlspecialchars($nota['ApellidoDocente'] . ' ' . $nota['NombreDocente'] . ' - ' . $nota['NombreCurso']) ?></td>
                                <td>
                                    <a href="RegistroNotas.php?edit=<?= $nota['idRegNota'] ?>" class="action-link">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="index.php" class="back-button">Volver al Menú Principal</a>
        </div>
    </div>
</body>
</html>
