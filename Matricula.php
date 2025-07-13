<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once 'includes/funciones.php';
$conn = get_db_connection();

// 1. Eliminar matrícula
if (isset($_GET['delete'])) {
    $idMatricula = $_GET['delete'];
    $sql = "DELETE FROM matricula WHERE idMatricula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idMatricula);
    $stmt->execute();
    $stmt->close();
    header('Location: Matricula.php');
    exit();
}

// 2. Insertar matrícula
if (isset($_POST['registrar'])) {
    $idPersona = $_POST['idPersona'];
    $idSeccion = $_POST['idSeccion'];
    $fecha = $_POST['fechaMatricula'];

    $sql = "INSERT INTO matricula (fechaMatricula, idPersona, idSeccion) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $fecha, $idPersona, $idSeccion);
    
    try {
        if ($stmt->execute()) {
            header('Location: Matricula.php');
            exit();
        } else {
            $error = "Error al registrar la matrícula: " . $conn->error;
        }
    } catch (mysqli_sql_exception $e) {
        $error = $e->getMessage();
    } finally {
        $stmt->close();
    }
}

// 3. Obtener estudiantes NO matriculados
$estudiantes = [];
$sqlEstudiantes = "SELECT p.idPersona, p.Nombres, p.Apellido_Paterno, p.Apellido_Materno
                   FROM persona p
                   JOIN estudiante e ON p.idPersona = e.idPersona
                   WHERE p.idPersona NOT IN (SELECT idPersona FROM matricula)";
$result = $conn->query($sqlEstudiantes);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $estudiantes[] = $row;
    }
} else {
    $error_estudiantes = "No hay estudiantes disponibles para matrícula. Verifique que:";
    $error_estudiantes .= "<br>- Existan estudiantes registrados en el sistema";
    $error_estudiantes .= "<br>- Los estudiantes no estén ya matriculados";
    $error_estudiantes .= "<br>- La tabla 'estudiante' contenga registros válidos";
}

// 4. Obtener secciones
$secciones = [];
$sqlSecciones = "SELECT s.idSeccion, g.NombreGrado 
                 FROM seccion s
                 JOIN grado g ON s.idGrado = g.idGrado";
$result = $conn->query($sqlSecciones);
while ($row = $result->fetch_assoc()) {
    $secciones[] = $row;
}

// 5. Obtener matrículas registradas
$matriculas = [];
$sqlMatriculas = "SELECT m.idMatricula, m.fechaMatricula,
                         p.Nombres, p.Apellido_Paterno, p.Apellido_Materno,
                         s.idSeccion, g.NombreGrado
                  FROM matricula m
                  JOIN persona p ON m.idPersona = p.idPersona
                  JOIN seccion s ON m.idSeccion = s.idSeccion
                  JOIN grado g ON s.idGrado = g.idGrado
                  ORDER BY g.NombreGrado, p.Apellido_Paterno";
$result = $conn->query($sqlMatriculas);
while ($row = $result->fetch_assoc()) {
    $matriculas[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Matrícula de Estudiantes</title>
    <link rel="stylesheet" href="css/matricula_style.css">
</head>
<body>
    <div class="container">
        <h1>Matrícula de Estudiantes</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="form-section">
            <h2>Registrar Nueva Matrícula</h2>
            <form method="POST" action="Matricula.php">
                <div class="form-group">
                    <label for="idPersona">Estudiante:</label>
                    <select name="idPersona" id="idPersona" required>
                        <option value="">-- Seleccione --</option>
                        <?php if (!empty($estudiantes)): ?>
                            <?php foreach ($estudiantes as $est): ?>
                                <option value="<?php echo $est['idPersona']; ?>">
                                    <?php echo htmlspecialchars($est['Nombres'] . ' ' . $est['Apellido_Paterno'] . ' ' . $est['Apellido_Materno']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled selected>-- No hay estudiantes disponibles --</option>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($error_estudiantes)): ?>
                        <div class="error-message" style="margin-top: 10px;">
                            <?php echo $error_estudiantes; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="idSeccion">Sección:</label>
                    <select name="idSeccion" id="idSeccion" required>
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($secciones as $sec): ?>
                            <option value="<?php echo $sec['idSeccion']; ?>">
                                Sección <?php echo $sec['idSeccion']; ?> (<?php echo $sec['NombreGrado']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fechaMatricula">Fecha de Matrícula:</label>
                    <input type="date" name="fechaMatricula" id="fechaMatricula" required>
                </div>
                
                <button type="submit" name="registrar" class="btn btn-primary">Registrar Matrícula</button>
            </form>
        </div>
        
        <h2>Matrículas Registradas</h2>
        <?php if (!empty($matriculas)): ?>
            <?php
            $currentGrado = null;
            foreach ($matriculas as $mat):
                if ($currentGrado != $mat['NombreGrado']):
                    $currentGrado = $mat['NombreGrado'];
            ?>
                <div class="grado-header">Grado: <?php echo $currentGrado; ?></div>
                <table>
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Sección</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php endif; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mat['Nombres'] . ' ' . $mat['Apellido_Paterno'] . ' ' . $mat['Apellido_Materno']); ?></td>
                            <td>Sección <?php echo htmlspecialchars($mat['idSeccion']); ?></td>
                            <td><?php echo htmlspecialchars($mat['fechaMatricula']); ?></td>
                            <td>
                                <a href="Matricula.php?delete=<?php echo $mat['idMatricula']; ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('¿Está seguro de eliminar esta matrícula?')">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
            <?php 
                if (next($matriculas) === false || current($matriculas)['NombreGrado'] != $currentGrado):
            ?>
                    </tbody>
                </table>
            <?php
                endif;
            endforeach;
            ?>
        <?php else: ?>
            <p>No hay matrículas registradas.</p>
        <?php endif; ?>
        
        <a href="index.php" class="btn btn-secondary">Volver al Menú Principal</a>
    </div>
</body>
</html>