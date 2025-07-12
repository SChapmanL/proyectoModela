<?php
require_once 'includes/funciones.php';
$conn = get_db_connection();

if (isset($_POST['add'])) {
    $nota = $_POST['Nota'];
    $fechaEvaluacion = $_POST['FechaEvaluacion'];
    $tipoEvaluacion = $_POST['TipoEvaluacion'];
    $bimestre = $_POST['Bimestre'];
    $idMatricula = $_POST['idMatricula'];
    $idImparte = $_POST['idImparte'];

    $sql = "INSERT INTO RegNotas (Nota, FechaEvaluacion, TipoEvaluacion, Bimestre, idMatricula, idImparte)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dssiii", $nota, $fechaEvaluacion, $tipoEvaluacion, $bimestre, $idMatricula, $idImparte);
    $stmt->execute();
    $stmt->close();

    header("Location: RegistroNotas.php");
    exit();
}

$notas = [];
$sql = "SELECT * FROM RegNotas";
$result = $conn->query($sql);
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
            <h2>Registrar Nota</h2>
            <form action="RegistroNotas.php" method="POST">
                <div class="form-grid">
                    <input type="number" name="Nota" step="0.01" placeholder="Nota (ej. 15.50)" required>
                    <input type="date" name="FechaEvaluacion" required>
                    <input type="text" name="TipoEvaluacion" placeholder="Tipo de Evaluación (Parcial, Final...)" required>
                    <input type="number" name="Bimestre" min="1" max="4" placeholder="Bimestre" required>

                    <!-- Select de Matrícula -->
                    <select name="idMatricula" required>
                        <option value="">Seleccione Matrícula</option>
                        <?php
                        $matriculas = $conn->query("SELECT idMatricula FROM matricula");
                        while ($row = $matriculas->fetch_assoc()) {
                            echo "<option value='{$row['idMatricula']}'>ID Matrícula: {$row['idMatricula']}</option>";
                        }
                        ?>
                    </select>

                    <!-- Select de Docente Imparte -->
                    <select name="idImparte" required>
                        <option value="">Seleccione Docente/Curso</option>
                        <?php
                        $impartes = $conn->query("SELECT idImparte, idPersona, idCurso FROM DocenteImparteCurso");
                        while ($row = $impartes->fetch_assoc()) {
                            echo "<option value='{$row['idImparte']}'>ID Imparte: {$row['idImparte']} | Docente: {$row['idPersona']} | Curso: {$row['idCurso']}</option>";
                        }
                        $conn->close();
                        ?>
                    </select>
                </div>
                <input type="submit" name="add" value="Registrar Nota" class="form-submit-button">
            </form>
        </div>

        <div class="table-section">
            <h2>Notas Registradas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID Nota</th>
                        <th>Nota</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Bimestre</th>
                        <th>ID Matrícula</th>
                        <th>ID Imparte</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($notas)): ?>
                        <tr><td colspan="7">No hay registros aún.</td></tr>
                    <?php else: ?>
                        <?php foreach ($notas as $nota): ?>
                            <tr>
                                <td><?= htmlspecialchars($nota['idRegNota']) ?></td>
                                <td><?= htmlspecialchars($nota['Nota']) ?></td>
                                <td><?= htmlspecialchars($nota['FechaEvaluacion']) ?></td>
                                <td><?= htmlspecialchars($nota['TipoEvaluacion']) ?></td>
                                <td><?= htmlspecialchars($nota['Bimestre']) ?></td>
                                <td><?= htmlspecialchars($nota['idMatricula']) ?></td>
                                <td><?= htmlspecialchars($nota['idImparte']) ?></td>
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
