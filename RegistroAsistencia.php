<?php
require_once 'includes/funciones.php';
$conn = get_db_connection();

if (isset($_POST['add'])) {
    $estado = $_POST['Estado'];
    $fecha = $_POST['FechaAsistencia'];
    $hora = $_POST['Hora_Llegada'];
    $idMatricula = $_POST['idMatricula'];
    $idAño = $_POST['idAño'];

    $sql = "INSERT INTO RegistroAsistencia (Estado, FechaAsistencia, Hora_Llegada, idMatricula, idAño)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $estado, $fecha, $hora, $idMatricula, $idAño);
    $stmt->execute();
    $stmt->close();

    header("Location: RegistroAsistencia.php");
    exit();
}

// Obtener asistencias para mostrar en tabla
$asistencias = [];
$result = $conn->query("SELECT * FROM RegistroAsistencia");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $asistencias[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Asistencia</title>
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
            <h2>Registrar Asistencia</h2>
            <form action="RegistroAsistencia.php" method="POST">
                <div class="form-grid">
                    <select name="Estado" required>
                        <option value="">Seleccione Estado</option>
                        <option value="Asistió">Asistió</option>
                        <option value="Tardanza">Tardanza</option>
                        <option value="Falta">Falta</option>
                    </select>
                    <input type="date" name="FechaAsistencia" required>
                    <input type="time" name="Hora_Llegada" required>

                    <select name="idMatricula" required>
                        <option value="">Seleccione Matrícula</option>
                        <?php
                        $matriculas = $conn->query("SELECT idMatricula FROM matricula");
                        while ($row = $matriculas->fetch_assoc()) {
                            echo "<option value='{$row['idMatricula']}'>Matrícula: {$row['idMatricula']}</option>";
                        }
                        ?>
                    </select>

                    <select name="idAño" required>
                        <option value="">Seleccione Año Escolar</option>
                        <?php
                        $años = $conn->query("SELECT idAño FROM AñoEscolar");
                        while ($row = $años->fetch_assoc()) {
                            echo "<option value='{$row['idAño']}'>Año: {$row['idAño']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <input type="submit" name="add" value="Registrar Asistencia" class="form-submit-button">
            </form>
        </div>

        <div class="table-section">
            <h2>Asistencias Registradas</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Hora Llegada</th>
                        <th>Matrícula</th>
                        <th>Año Escolar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($asistencias)): ?>
                        <tr><td colspan="6">No hay asistencias registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($asistencias as $asistencia): ?>
                            <tr>
                                <td><?= htmlspecialchars($asistencia['idAsistenciaMes']) ?></td>
                                <td><?= htmlspecialchars($asistencia['Estado']) ?></td>
                                <td><?= htmlspecialchars($asistencia['FechaAsistencia']) ?></td>
                                <td><?= htmlspecialchars($asistencia['Hora_Llegada']) ?></td>
                                <td><?= htmlspecialchars($asistencia['idMatricula']) ?></td>
                                <td><?= htmlspecialchars($asistencia['idAño']) ?></td>
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
