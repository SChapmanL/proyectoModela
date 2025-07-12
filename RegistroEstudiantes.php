<?php
require_once 'includes/funciones.php';

// Inicializar variables
$error = '';
$success = '';
$nombres = '';
$apellido_paterno = '';
$apellido_materno = '';
$dni = '';
$direccion = '';
$codAlumno = '';
$contacto_emergencia = '';

// Conexión a la base de datos
$conn = get_db_connection();

// Función para generar código de alumno automático
function generarCodigoAlumno($conn) {
    $prefix = 'NIDO' . date('Y') . '-';
    $sql = "SELECT MAX(CAST(SUBSTRING(codAlumno, 10) AS UNSIGNED)) as max_num FROM estudiante WHERE codAlumno LIKE '$prefix%'";
    $result = $conn->query($sql);
    $next_num = 1;
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['max_num'] !== null) {
            $next_num = $row['max_num'] + 1;
        }
    }
    
    return $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
}

// Procesar eliminación de estudiante
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar'])) {
    $idPersona = $_POST['idPersona'];
    
    try {
        // Iniciar transacción
        $conn->begin_transaction();
        
        // Eliminar de estudiante
        $sql_eliminar_estudiante = "DELETE FROM estudiante WHERE idPersona = ?";
        $stmt_estudiante = $conn->prepare($sql_eliminar_estudiante);
        $stmt_estudiante->bind_param("i", $idPersona);
        
        if (!$stmt_estudiante->execute()) {
            throw new Exception("Error al eliminar el estudiante: " . $conn->error);
        }
        
        // Eliminar de persona
        $sql_eliminar_persona = "DELETE FROM persona WHERE idPersona = ?";
        $stmt_persona = $conn->prepare($sql_eliminar_persona);
        $stmt_persona->bind_param("i", $idPersona);
        
        if (!$stmt_persona->execute()) {
            throw new Exception("Error al eliminar los datos personales: " . $conn->error);
        }
        
        $conn->commit();
        $success = "Estudiante eliminado exitosamente";
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Procesar registro de nuevo estudiante
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {
    $nombres = trim($_POST['nombres']);
    $apellido_paterno = trim($_POST['apellido_paterno']);
    $apellido_materno = trim($_POST['apellido_materno']);
    $dni = trim($_POST['dni']);
    $direccion = trim($_POST['direccion']);
    $codAlumno = trim($_POST['codAlumno']);
    $contacto_emergencia = trim($_POST['contacto_emergencia']);

    try {
        // Validar datos
        if (empty($nombres) || empty($apellido_paterno) || empty($dni) || empty($codAlumno) || empty($contacto_emergencia)) {
            throw new Exception("Todos los campos marcados con * son obligatorios");
        }

        // Verificar si el DNI ya existe
        $sql_check = "SELECT idPersona FROM persona WHERE DNI = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $dni);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows > 0) {
            throw new Exception("El DNI ya está registrado en el sistema");
        }

        // Verificar si el contacto de emergencia existe
        $sql_check_contacto = "SELECT idPersona FROM persona WHERE idPersona = ?";
        $stmt_check_contacto = $conn->prepare($sql_check_contacto);
        $stmt_check_contacto->bind_param("i", $contacto_emergencia);
        $stmt_check_contacto->execute();
        
        if ($stmt_check_contacto->get_result()->num_rows == 0) {
            throw new Exception("El ID de contacto de emergencia no existe");
        }

        // Verificar si el código de alumno ya existe
        $sql_check_cod = "SELECT idPersona FROM estudiante WHERE codAlumno = ?";
        $stmt_check_cod = $conn->prepare($sql_check_cod);
        $stmt_check_cod->bind_param("s", $codAlumno);
        $stmt_check_cod->execute();
        
        if ($stmt_check_cod->get_result()->num_rows > 0) {
            throw new Exception("El código de alumno ya está en uso");
        }

        // Iniciar transacción
        $conn->begin_transaction();

        // Insertar nueva persona (sin teléfono)
        $sql_persona = "INSERT INTO persona (DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, contactoEmergencia_idPersona) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_persona = $conn->prepare($sql_persona);
        $stmt_persona->bind_param("sssssi", $dni, $nombres, $apellido_paterno, $apellido_materno, $direccion, $contacto_emergencia);
        
        if (!$stmt_persona->execute()) {
            throw new Exception("Error al registrar la persona: " . $conn->error);
        }

        // Obtener ID de la persona insertada
        $idPersona = $conn->insert_id;

        // Registrar como estudiante
        $sql_estudiante = "INSERT INTO estudiante (idPersona, codAlumno) VALUES (?, ?)";
        $stmt_estudiante = $conn->prepare($sql_estudiante);
        $stmt_estudiante->bind_param("is", $idPersona, $codAlumno);
        
        if (!$stmt_estudiante->execute()) {
            throw new Exception("Error al registrar el estudiante: " . $conn->error);
        }

        $conn->commit();
        $success = "Estudiante registrado exitosamente con código: " . htmlspecialchars($codAlumno);
        
        // Limpiar formulario después de registro exitoso
        $nombres = $apellido_paterno = $apellido_materno = $dni = $direccion = $contacto_emergencia = '';
        $codAlumno = generarCodigoAlumno($conn);

    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Obtener lista de contactos de emergencia disponibles
$contactos_disponibles = [];
$sql_contactos = "SELECT idPersona, CONCAT(Nombres, ' ', Apellido_Paterno, ' ', Apellido_Materno) as nombre_completo 
                  FROM persona 
                  ORDER BY Apellido_Paterno, Nombres";
$result_contactos = $conn->query($sql_contactos);

if ($result_contactos && $result_contactos->num_rows > 0) {
    while ($row = $result_contactos->fetch_assoc()) {
        $contactos_disponibles[] = $row;
    }
}

// Obtener lista de estudiantes existentes (sin teléfono)
$estudiantes = [];
$sql_estudiantes = "SELECT e.codAlumno, p.idPersona, p.DNI, p.Nombres, p.Apellido_Paterno, p.Apellido_Materno, 
                    p.Direccion, p.contactoEmergencia_idPersona,
                    CONCAT(ce.Nombres, ' ', ce.Apellido_Paterno) as contacto_emergencia
                    FROM estudiante e
                    JOIN persona p ON e.idPersona = p.idPersona
                    LEFT JOIN persona ce ON p.contactoEmergencia_idPersona = ce.idPersona
                    ORDER BY p.Apellido_Paterno, p.Nombres";
$result_estudiantes = $conn->query($sql_estudiantes);

if ($result_estudiantes && $result_estudiantes->num_rows > 0) {
    while ($row = $result_estudiantes->fetch_assoc()) {
        $estudiantes[] = $row;
    }
}

// Generar código de alumno inicial
if (empty($codAlumno)) {
    $codAlumno = generarCodigoAlumno($conn);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Estudiantes</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        label.required::after {
            content: " *";
            color: #e74c3c;
        }
        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        button.eliminar {
            background-color: #e74c3c;
        }
        button.eliminar:hover {
            background-color: #c0392b;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .info-box {
            background-color: #e7f4ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .acciones {
            white-space: nowrap;
        }
        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button">Volver al Menú Principal</a>
        
        <h1>Registro de Estudiantes</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <strong>Información importante:</strong> Debe seleccionar un contacto de emergencia válido (persona ya registrada en el sistema).
        </div>
        
        <div class="form-section">
            <h2>Registrar Nuevo Estudiante</h2>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombres" class="required">Nombres:</label>
                        <input type="text" name="nombres" id="nombres" value="<?php echo htmlspecialchars($nombres); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="apellido_paterno" class="required">Apellido Paterno:</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno" value="<?php echo htmlspecialchars($apellido_paterno); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="apellido_materno">Apellido Materno:</label>
                        <input type="text" name="apellido_materno" id="apellido_materno" value="<?php echo htmlspecialchars($apellido_materno); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="dni" class="required">DNI:</label>
                        <input type="text" name="dni" id="dni" value="<?php echo htmlspecialchars($dni); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" name="direccion" id="direccion" value="<?php echo htmlspecialchars($direccion); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="codAlumno" class="required">Código de Alumno:</label>
                        <input type="text" name="codAlumno" id="codAlumno" value="<?php echo htmlspecialchars($codAlumno); ?>" required>
                        <small>Formato: NIDOYYYY-NNN (ej. NIDO2023-001)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="contacto_emergencia" class="required">Contacto de Emergencia:</label>
                        <select name="contacto_emergencia" id="contacto_emergencia" required>
                            <option value="">-- Seleccione un contacto --</option>
                            <?php foreach ($contactos_disponibles as $contacto): ?>
                                <option value="<?php echo $contacto['idPersona']; ?>" <?php echo ($contacto['idPersona'] == $contacto_emergencia) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($contacto['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Seleccione una persona ya registrada en el sistema</small>
                    </div>
                </div>
                
                <button type="submit" name="registrar">Registrar Estudiante</button>
            </form>
        </div>
        
        <div class="students-list">
            <h2 class="section-title">Estudiantes Registrados</h2>
            
            <?php if (empty($estudiantes)): ?>
                <p>No hay estudiantes registrados aún.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>DNI</th>
                            <th>Dirección</th>
                            <th>Contacto Emergencia</th>
                            <th class="acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $estudiante): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($estudiante['codAlumno']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['Nombres']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['Apellido_Paterno'] . ' ' . $estudiante['Apellido_Materno']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['DNI']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['Direccion']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['contacto_emergencia'] ?? 'No especificado'); ?></td>
                                <td class="acciones">
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="idPersona" value="<?php echo $estudiante['idPersona']; ?>">
                                        <button type="submit" name="eliminar" class="eliminar" onclick="return confirm('¿Está seguro que desea eliminar este estudiante? Esta acción no se puede deshacer.');">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>