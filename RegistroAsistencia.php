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
$sql_select = "SELECT ra.*, p.Nombres, p.Apellido_Paterno, EXTRACT(YEAR FROM ae.FechaInicio) AS Anio
               FROM RegistroAsistencia ra
               JOIN matricula m ON ra.idMatricula = m.idMatricula
               JOIN Estudiante e ON m.idPersona = e.idPersona
               JOIN Persona p ON e.idPersona = p.idPersona
               JOIN AñoEscolar ae ON ra.idAño = ae.idAño";

$where_clause = "";
$params = [];
$param_types = "";

if (isset($_GET['filter_idSeccion']) && $_GET['filter_idSeccion'] != '') {
    $idSeccion_filter = $_GET['filter_idSeccion'];
    $sql_select .= " JOIN Seccion s ON m.idSeccion = s.idSeccion WHERE s.idSeccion = ?";
    $params[] = $idSeccion_filter;
    $param_types .= "i";
}

$stmt_select = $conn->prepare($sql_select);
if (!empty($params)) {
    $stmt_select->bind_param($param_types, ...$params);
}
$stmt_select->execute();
$result = $stmt_select->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $asistencias[] = $row;
    }
}
$stmt_select->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Asistencia</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/registroasistencia_style.css">
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

                    <select name="idMatricula" id="idMatriculaSelect" required>
                        <option value="">Seleccione Estudiante</option>
                        <?php
                        $matriculas = $conn->query("SELECT m.idMatricula, p.Nombres, p.Apellido_Paterno FROM matricula m JOIN Estudiante e ON m.idPersona = e.idPersona JOIN Persona p ON e.idPersona = p.idPersona");
                        while ($row = $matriculas->fetch_assoc()) {
                            echo "<option value='{$row['idMatricula']}'>{$row['Nombres']} {$row['Apellido_Paterno']}</option>";
                        }
                        ?>
                    </select>

                    <select name="idAño" required>
                        <option value="">Seleccione Año Escolar</option>
                        <?php
                        $años = $conn->query("SELECT idAño, FechaInicio FROM AñoEscolar");
                        while ($row = $años->fetch_assoc()) {
                            echo "<option value='{$row['idAño']}'>Año: " . date('Y', strtotime($row['FechaInicio'])) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <input type="submit" name="add" value="Registrar Asistencia" class="form-submit-button">
            </form>
        </div>

        <div class="table-section">
            <h2>Asistencias Registradas</h2>
            <form action="RegistroAsistencia.php" method="GET" style="margin-bottom: 20px;">
                <label for="filter_idSeccion">Filtrar por Sección:</label>
                <select name="filter_idSeccion" id="filter_idSeccion">
                    <option value="">Todas las Secciones</option>
                    <?php
                    $secciones = $conn->query("SELECT s.idSeccion, g.NombreGrado FROM Seccion s JOIN Grado g ON s.idGrado = g.idGrado");
                    while ($row = $secciones->fetch_assoc()) {
                        $selected = (isset($_GET['filter_idSeccion']) && $_GET['filter_idSeccion'] == $row['idSeccion']) ? 'selected' : '';
                        echo "<option value='{$row['idSeccion']}' {$selected}>{$row['NombreGrado']} (Sección: {$row['idSeccion']})</option>";
                    }
                    ?>
                </select>
                <input type="submit" value="Filtrar" class="form-submit-button" style="width: auto; padding: 8px 15px; margin-left: 10px;">
            </form>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Hora Llegada</th>
                        <th>Estudiante</th>
                        <th>Año</th>
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
                                <td><?= htmlspecialchars($asistencia['Nombres']) ?> <?= htmlspecialchars($asistencia['Apellido_Paterno']) ?></td>
                                <td><?= htmlspecialchars($asistencia['Anio']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="index.php" class="back-button">Volver al Menú Principal</a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#idMatriculaSelect').select2();
        });
    </script>
</body>
</html>
