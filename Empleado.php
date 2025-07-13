<?php
// necesita el funciones.php
require_once 'includes/funciones.php';

$conn = get_db_connection();
$trigger_error = '';

// para agilizar la busqueda en la combobox de secciones
$secciones = [];
// Seleccionar el grado y la seccion de ese mismo grado en caso haya
$sql_secciones = "SELECT s.idSeccion, g.NombreGrado FROM Seccion s JOIN Grado g ON s.idGrado = g.idGrado WHERE g.NombreGrado IN ('Naranjitas', 'Fresitas', 'Bananitas', 'Peritas')";
$result_secciones = $conn->query($sql_secciones);

if ($result_secciones->num_rows > 0) {
    while($row_seccion = $result_secciones->fetch_assoc()) {
        $secciones[] = $row_seccion;
    }
}

// Inicializacion de variables
$id = '';
$DNI = '';
$Nombres = '';
$Apellido_Paterno = '';
$Apellido_Materno = '';
$Direccion = '';
$Sexo = '';
$FechaNac = '';
$Telefono = '';
$contactoEmergencia_idPersona = '';

$Correo = '';
$GradoInstruccion = '';

$CodEmpleado = '';
$CondicionGrupoRiesgo = '';
$Cargo = '';
$Salario = '';

$Modalidad = '';
$idSeccion = '';

// --- CRUD ---

// 1. INSERCION DE Empleados
if (isset($_POST['add'])) {
    $conn->begin_transaction();
    try {
        // Persona data
        $DNI = $_POST['DNI'] ?? '';
        $Nombres = $_POST['Nombres'] ?? '';
        $Apellido_Paterno = $_POST['Apellido_Paterno'] ?? '';
        $Apellido_Materno = $_POST['Apellido_Materno'] ?? '';
        $Direccion = $_POST['Direccion'] ?? '';
        $Sexo = $_POST['Sexo'] ?? '';
        $FechaNac = $_POST['FechaNac'] ?? '';
        $Telefono = $_POST['Telefono'] ?? '';
        $contactoEmergencia_idPersona = $_POST['contactoEmergencia_idPersona'] ?? null;

        // No_Estudiante
        $Correo = $_POST['Correo'] ?? '';
        $GradoInstruccion = $_POST['GradoInstruccion'] ?? '';

        // Empleado
        $CodEmpleado = $_POST['CodEmpleado'] ?? '';
        $CondicionGrupoRiesgo = $_POST['CondicionGrupoRiesgo'] ?? '';
        $Cargo = $_POST['Cargo'] ?? '';
        $Salario = $_POST['Salario'] ?? '';

        // Academico
        $Modalidad = $_POST['Modalidad'] ?? '';

        // Docente
        $idSeccion = $_POST['idSeccion'] ?? null;

        // Insert into Persona
        $sql_persona = "INSERT INTO Persona (DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, Sexo, FechaNac, telefono, contactoEmergencia_idPersona) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_persona = $conn->prepare($sql_persona);
        $stmt_persona->bind_param("sssssssii", $DNI, $Nombres, $Apellido_Paterno, $Apellido_Materno, $Direccion, $Sexo, $FechaNac, $Telefono, $contactoEmergencia_idPersona);
        $stmt_persona->execute();
        $idPersona = $conn->insert_id; // obtener el ultimo id ingresado
        $stmt_persona->close();

        // Insert into No_Estudiante
        $sql_no_estudiante = "INSERT INTO No_Estudiante (correo, gradoInstruccion, idPersona) VALUES (?, ?, ?)";
        $stmt_no_estudiante = $conn->prepare($sql_no_estudiante);
        $stmt_no_estudiante->bind_param("ssi", $Correo, $GradoInstruccion, $idPersona);
        $stmt_no_estudiante->execute();
        $stmt_no_estudiante->close();

        // Insert into Empleado
        $sql_empleado = "INSERT INTO Empleado (codEmpleado, CondicionGrupoRiesgo, cargo, Salario, idPersona) VALUES (?, ?, ?, ?, ?)";
        $stmt_empleado = $conn->prepare($sql_empleado);
        $stmt_empleado->bind_param("sssdi", $CodEmpleado, $CondicionGrupoRiesgo, $Cargo, $Salario, $idPersona);
        $stmt_empleado->execute();
        $stmt_empleado->close();

        // Condicionales basados en el Cargo y sus diferentes opciones
        if ($Cargo == 'Administrativo') {
            $sql_administrativo = "INSERT INTO Administrativo (idPersona) VALUES (?)";
            $stmt_administrativo = $conn->prepare($sql_administrativo);
            $stmt_administrativo->bind_param("i", $idPersona);
            $stmt_administrativo->execute();
            $stmt_administrativo->close();
        } elseif ($Cargo == 'Docente' || $Cargo == 'Auxiliar') {
            $sql_academico = "INSERT INTO Academico (Modalidad, idPersona) VALUES (?, ?)";
            $stmt_academico = $conn->prepare($sql_academico);
            $stmt_academico->bind_param("si", $Modalidad, $idPersona);
            $stmt_academico->execute();
            $stmt_academico->close();

            if ($Cargo == 'Docente') {
                $sql_docente = "INSERT INTO Docente (idPersona, idSeccion) VALUES (?, ?)";
                $stmt_docente = $conn->prepare($sql_docente);
                $stmt_docente->bind_param("ii", $idPersona, $idSeccion);
                $stmt_docente->execute();
                $stmt_docente->close();
            } elseif ($Cargo == 'Auxiliar') {
                $sql_auxiliar = "INSERT INTO Auxiliar (idPersona, idSeccion) VALUES (?, ?)";
                $stmt_auxiliar = $conn->prepare($sql_auxiliar);
                $stmt_auxiliar->bind_param("ii", $idPersona, $idSeccion);
                $stmt_auxiliar->execute();
                $stmt_auxiliar->close();
            }
        }
        $conn->commit();
        header('Location: Empleado.php'); // Redirect to refresh page
        exit();
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        if (strpos($e->getMessage(), 'control_horario_persona') !== false) {
            $trigger_error = $e->getMessage();
        } else {
            echo "Error al registrar: " . $e->getMessage();
        }
    }
}

// 2. Read/Fetch Empleado for Update
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    // Un join de Persona, No_Estudiante, Empleado, Academico, Docente, Auxiliar
    $sql = "SELECT 
                p.idPersona, p.DNI, p.Nombres, p.Apellido_Paterno, p.Apellido_Materno, p.Direccion, p.Sexo, p.FechaNac, p.telefono, p.contactoEmergencia_idPersona,
                ne.correo, ne.gradoInstruccion,
                e.codEmpleado, e.CondicionGrupoRiesgo, e.cargo, e.Salario,
                a.Modalidad,
                d.idSeccion AS Docente_idSeccion,
                aux.idSeccion AS Auxiliar_idSeccion
            FROM Persona p
            JOIN No_Estudiante ne ON p.idPersona = ne.idPersona
            JOIN Empleado e ON ne.idPersona = e.idPersona
            LEFT JOIN Academico a ON e.idPersona = a.idPersona
            LEFT JOIN Docente d ON a.idPersona = d.idPersona
            LEFT JOIN Auxiliar aux ON a.idPersona = aux.idPersona
            WHERE p.idPersona = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['idPersona'];
        $DNI = $row['DNI'];
        $Nombres = $row['Nombres'];
        $Apellido_Paterno = $row['Apellido_Paterno'];
        $Apellido_Materno = $row['Apellido_Materno'];
        $Direccion = $row['Direccion'];
        $Sexo = $row['Sexo'];
        $FechaNac = $row['FechaNac'];
        $Telefono = $row['telefono'];
        $contactoEmergencia_idPersona = $row['contactoEmergencia_idPersona'];
        $Correo = $row['correo'];
        $GradoInstruccion = $row['gradoInstruccion'];
        $CodEmpleado = $row['codEmpleado'];
        $CondicionGrupoRiesgo = $row['CondicionGrupoRiesgo'];
        $Cargo = $row['cargo'];
        $Salario = $row['Salario'];
        $Modalidad = $row['Modalidad'];
        $idSeccion = $row['Docente_idSeccion'] ?? $row['Auxiliar_idSeccion'];
    }
    $stmt->close();
}

// 3. Update Empleado
if (isset($_POST['update'])) {
    $id = $_POST['id'] ?? '';
    $DNI = $_POST['DNI'] ?? '';
    $Nombres = $_POST['Nombres'] ?? '';
    $Apellido_Paterno = $_POST['Apellido_Paterno'] ?? '';
    $Apellido_Materno = $_POST['Apellido_Materno'] ?? '';
    $Direccion = $_POST['Direccion'] ?? '';
    $Sexo = $_POST['Sexo'] ?? '';
    $FechaNac = $_POST['FechaNac'] ?? '';
    $Telefono = $_POST['Telefono'] ?? '';
    $contactoEmergencia_idPersona = $_POST['contactoEmergencia_idPersona'] ?? null;
    $Correo = $_POST['Correo'] ?? '';
    $GradoInstruccion = $_POST['GradoInstruccion'] ?? '';
    $CodEmpleado = $_POST['CodEmpleado'] ?? '';
    $CondicionGrupoRiesgo = $_POST['CondicionGrupoRiesgo'] ?? '';
    $Cargo = $_POST['Cargo'] ?? '';
    $Salario = $_POST['Salario'] ?? '';
    $Modalidad = $_POST['Modalidad'] ?? '';
    $idSeccion = $_POST['idSeccion'] ?? null;

    // Update Persona
    $sql_persona = "UPDATE Persona SET DNI = ?, Nombres = ?, Apellido_Paterno = ?, Apellido_Materno = ?, Direccion = ?, Sexo = ?, FechaNac = ?, telefono = ?, contactoEmergencia_idPersona = ? WHERE idPersona = ?";
    $stmt_persona = $conn->prepare($sql_persona);
    $stmt_persona->bind_param("sssssssiii", $DNI, $Nombres, $Apellido_Paterno, $Apellido_Materno, $Direccion, $Sexo, $FechaNac, $Telefono, $contactoEmergencia_idPersona, $id);
    $stmt_persona->execute();
    $stmt_persona->close();

    // Update No_Estudiante
    $sql_no_estudiante = "UPDATE No_Estudiante SET correo = ?, gradoInstruccion = ? WHERE idPersona = ?";
    $stmt_no_estudiante = $conn->prepare($sql_no_estudiante);
    $stmt_no_estudiante->bind_param("ssi", $Correo, $GradoInstruccion, $id);
    $stmt_no_estudiante->execute();
    $stmt_no_estudiante->close();

    // Update Empleado
    $sql_empleado = "UPDATE Empleado SET codEmpleado = ?, CondicionGrupoRiesgo = ?, cargo = ?, Salario = ? WHERE idPersona = ?";
    $stmt_empleado = $conn->prepare($sql_empleado);
    $stmt_empleado->bind_param("sssdi", $CodEmpleado, $CondicionGrupoRiesgo, $Cargo, $Salario, $id);
    $stmt_empleado->execute();
    $stmt_empleado->close();

    // Updates condicionales dependiendo de elecciones de Cargo
    if ($Cargo == 'Administrativo') {
        $sql_administrativo = "REPLACE INTO Administrativo (idPersona) VALUES (?)";
        $stmt_administrativo = $conn->prepare($sql_administrativo);
        $stmt_administrativo->bind_param("i", $id);
        $stmt_administrativo->execute();
        $stmt_administrativo->close();

        // Delete from Academico, Docente, Auxiliar en caso exista ese id
        $conn->query("DELETE FROM Docente WHERE idPersona = $id");
        $conn->query("DELETE FROM Auxiliar WHERE idPersona = $id");
        $conn->query("DELETE FROM Academico WHERE idPersona = $id");

    } elseif ($Cargo == 'Docente' || $Cargo == 'Auxiliar') {
        $sql_academico = "REPLACE INTO Academico (Modalidad, idPersona) VALUES (?, ?)";
        $stmt_academico = $conn->prepare($sql_academico);
        $stmt_academico->bind_param("si", $Modalidad, $id);
        $stmt_academico->execute();
        $stmt_academico->close();

        // Delete from Administrativo si existe
        $conn->query("DELETE FROM Administrativo WHERE idPersona = $id");

        if ($Cargo == 'Docente') {
            $sql_docente = "REPLACE INTO Docente (idPersona, idSeccion) VALUES (?, ?)";
            $stmt_docente = $conn->prepare($sql_docente);
            $stmt_docente->bind_param("ii", $id, $idSeccion);
            $stmt_docente->execute();
            $stmt_docente->close();

            // Delete from Auxiliar si existe
            $conn->query("DELETE FROM Auxiliar WHERE idPersona = $id");

        } elseif ($Cargo == 'Auxiliar') {
            $sql_auxiliar = "REPLACE INTO Auxiliar (idPersona, idSeccion) VALUES (?, ?)";
            $stmt_auxiliar = $conn->prepare($sql_auxiliar);
            $stmt_auxiliar->bind_param("ii", $id, $idSeccion);
            $stmt_auxiliar->execute();
            $stmt_auxiliar->close();

            // Delete from Docente si existe
            $conn->query("DELETE FROM Docente WHERE idPersona = $id");
        }
    } else { // Si el Cargo es Administrativo, Docente o Auxiliar
        // Delete from Administrativo, Academico, Docente, Auxiliar si el registro existe
        $conn->query("DELETE FROM Administrativo WHERE idPersona = $id");
        $conn->query("DELETE FROM Docente WHERE idPersona = $id");
        $conn->query("DELETE FROM Auxiliar WHERE idPersona = $id");
        $conn->query("DELETE FROM Academico WHERE idPersona = $id");
    }

    header('Location: Empleado.php');
    exit();
}

// 4. Delete Empleado
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Delete from Auxiliar table first si existe
    // para hacer la correcta eliminacion va al reves
    $sql_delete_auxiliar = "DELETE FROM Auxiliar WHERE idPersona = ?";
    $stmt_delete_auxiliar = $conn->prepare($sql_delete_auxiliar);
    $stmt_delete_auxiliar->bind_param("i", $id);
    $stmt_delete_auxiliar->execute();
    $stmt_delete_auxiliar->close();

    // Delete from Docente si existe
    $sql_delete_docente = "DELETE FROM Docente WHERE idPersona = ?";
    $stmt_delete_docente = $conn->prepare($sql_delete_docente);
    $stmt_delete_docente->bind_param("i", $id);
    $stmt_delete_docente->execute();
    $stmt_delete_docente->close();

    // Delete from Academico si existe
    $sql_delete_academico = "DELETE FROM Academico WHERE idPersona = ?";
    $stmt_delete_academico = $conn->prepare($sql_delete_academico);
    $stmt_delete_academico->bind_param("i", $id);
    $stmt_delete_academico->execute();
    $stmt_delete_academico->close();

    // Delete from Administrativo
    $sql_delete_administrativo = "DELETE FROM Administrativo WHERE idPersona = ?";
    $stmt_delete_administrativo = $conn->prepare($sql_delete_administrativo);
    $stmt_delete_administrativo->bind_param("i", $id);
    $stmt_delete_administrativo->execute();
    $stmt_delete_administrativo->close();

    // Delete from Empleado
    $sql_delete_empleado = "DELETE FROM Empleado WHERE idPersona = ?";
    $stmt_delete_empleado = $conn->prepare($sql_delete_empleado);
    $stmt_delete_empleado->bind_param("i", $id);
    $stmt_delete_empleado->execute();
    $stmt_delete_empleado->close();

    // Delete from No_Estudiante
    $sql_delete_no_estudiante = "DELETE FROM No_Estudiante WHERE idPersona = ?";
    $stmt_delete_no_estudiante = $conn->prepare($sql_delete_no_estudiante);
    $stmt_delete_no_estudiante->bind_param("i", $id);
    $stmt_delete_no_estudiante->execute();
    $stmt_delete_no_estudiante->close();

    // Then delete from Persona
    $sql_delete_persona = "DELETE FROM Persona WHERE idPersona = ?";
    $stmt_delete_persona = $conn->prepare($sql_delete_persona);
    $stmt_delete_persona->bind_param("i", $id);
    $stmt_delete_persona->execute();
    $stmt_delete_persona->close();

    header('Location: Empleado.php');
    exit();
}

// SELECT de todos los Empleados para mostrarse
$empleados = [];
$sql = "SELECT 
            p.idPersona, p.DNI, p.Nombres, p.Apellido_Paterno, p.Apellido_Materno, p.Direccion, p.Sexo, p.FechaNac, p.telefono, p.contactoEmergencia_idPersona,
            ne.correo, ne.gradoInstruccion,
            e.codEmpleado, e.CondicionGrupoRiesgo, e.cargo, e.Salario,
            a.Modalidad,
            d.idSeccion AS Docente_idSeccion,
            aux.idSeccion AS Auxiliar_idSeccion
        FROM Persona p
        JOIN No_Estudiante ne ON p.idPersona = ne.idPersona
        JOIN Empleado e ON ne.idPersona = e.idPersona
        LEFT JOIN Academico a ON e.idPersona = a.idPersona
        LEFT JOIN Docente d ON a.idPersona = d.idPersona
        LEFT JOIN Auxiliar aux ON a.idPersona = aux.idPersona";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['Cargo'] = $row['cargo']; // Lleva 'cargo' de Empleado hasta un nuevo 'Cargo' para ser mostrado
        $row['Modalidad'] = $row['Modalidad']; // Modalidad del Academico
        $row['Grupo_Riesgo'] = $row['CondicionGrupoRiesgo']; // Lleva 'CondicionGrupoRiesgo' a 'Grupo_Riesgo'
        $empleados[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Personal</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/empleado_style.css">
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h2>Registrar Personal</h2>
            <form action="Empleado.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <div class="form-grid">
                    <input type="text" name="DNI" placeholder="DNI" value="<?php echo htmlspecialchars($DNI); ?>" required>
                    <input type="text" name="Nombres" placeholder="Nombres" value="<?php echo htmlspecialchars($Nombres); ?>" required>
                    <input type="text" name="Apellido_Paterno" placeholder="Apellido Paterno" value="<?php echo htmlspecialchars($Apellido_Paterno); ?>" required>
                    <input type="text" name="Apellido_Materno" placeholder="Apellido Materno" value="<?php echo htmlspecialchars($Apellido_Materno); ?>" required>
                    
                    <input type="text" name="Direccion" placeholder="Dirección" value="<?php echo htmlspecialchars($Direccion); ?>" class="form-full-width" required>
                    
                    <div class="radio-group form-full-width">
                        <label>Sexo:</label>
                        <input type="radio" id="masculino" name="Sexo" value="Masculino" <?php echo ($Sexo == 'Masculino') ? 'checked' : ''; ?> required>
                        <label for="masculino">Masculino</label>
                        <input type="radio" id="femenino" name="Sexo" value="Femenino" <?php echo ($Sexo == 'Femenino') ? 'checked' : ''; ?> required>
                        <label for="femenino">Femenino</label>
                    </div>

                    <input type="date" name="FechaNac" value="<?php echo htmlspecialchars($FechaNac); ?>" required>
                    <input type="text" name="Telefono" placeholder="Teléfono" value="<?php echo htmlspecialchars($Telefono); ?>" required>
                    <input type="number" name="contactoEmergencia_idPersona" placeholder="ID Contacto Emergencia" value="<?php echo htmlspecialchars($contactoEmergencia_idPersona); ?>">

                    <input type="email" name="Correo" placeholder="Correo Electrónico" value="<?php echo htmlspecialchars($Correo); ?>" required>
                    <input type="text" name="GradoInstruccion" placeholder="Grado de Instrucción" value="<?php echo htmlspecialchars($GradoInstruccion); ?>" required>

                    <input type="text" name="CodEmpleado" placeholder="Código de Empleado" value="<?php echo htmlspecialchars($CodEmpleado); ?>" required>
                    <input type="text" name="CondicionGrupoRiesgo" placeholder="Condición Grupo Riesgo" value="<?php echo htmlspecialchars($CondicionGrupoRiesgo); ?>" required>
                    <input type="number" name="Salario" placeholder="Salario" value="<?php echo htmlspecialchars($Salario); ?>" step="0.01" required>
                    
                    <select name="Cargo" id="cargoSelect" onchange="toggleCargoFields()">
                        <option value="">Seleccionar Cargo</option>
                        <option value="Administrativo" <?php echo ($Cargo == 'Administrativo') ? 'selected' : ''; ?>>Administrativo</option>
                        <option value="Docente" <?php echo ($Cargo == 'Docente') ? 'selected' : ''; ?>>Docente</option>
                        <option value="Auxiliar" <?php echo ($Cargo == 'Auxiliar') ? 'selected' : ''; ?>>Auxiliar</option>
                        <!-- 
                        <!<option value="Mantenimiento" <?php //echo ($Cargo == 'Mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                        -->
                        <option value="Otro" <?php echo ($Cargo == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>

                    <div id="academicoFields" style="display: none;">
                        <select name="Modalidad">
                            <option value="">Seleccionar Modalidad</option>
                            <option value="Virtual" <?php echo ($Modalidad == 'Virtual') ? 'selected' : ''; ?>>Virtual</option>
                            <option value="Presencial" <?php echo ($Modalidad == 'Presencial') ? 'selected' : ''; ?>>Presencial</option>
                            <option value="Hibrido" <?php echo ($Modalidad == 'Hibrido') ? 'selected' : ''; ?>>Hibrido</option>
                        </select>
                    </div>

                    <div id="docenteAuxiliarFields" style="display: none;">
                        <select name="idSeccion">
                            <option value="">Seleccionar Sección</option>
                            <?php foreach ($secciones as $seccion): ?>
                                <option value="<?php echo htmlspecialchars($seccion['idSeccion']); ?>" <?php echo ($idSeccion == $seccion['idSeccion']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($seccion['NombreGrado']) . ' (ID: ' . htmlspecialchars($seccion['idSeccion']) . ')'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-buttons">
                    <button type="button" class="back-button-form" onclick="window.location.href='index.php'">Volver</button>
                    <?php if ($id): ?>
                        <button type="submit" name="update" class="submit-button-form">Actualizar Personal</button>
                    <?php else: ?>
                        <button type="submit" name="add" class="submit-button-form">Registrar Personal</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <script>
            function toggleCargoFields() {
                var cargoSelect = document.getElementById('cargoSelect');
                var academicoFields = document.getElementById('academicoFields');
                var docenteAuxiliarFields = document.getElementById('docenteAuxiliarFields');

                if (cargoSelect.value === 'Docente' || cargoSelect.value === 'Auxiliar') {
                    academicoFields.style.display = 'block';
                    docenteAuxiliarFields.style.display = 'block';
                } else {
                    academicoFields.style.display = 'none';
                    docenteAuxiliarFields.style.display = 'none';
                }
            }

            document.addEventListener('DOMContentLoaded', toggleCargoFields);
        </script>

        <div class="table-section">
            <h2>Listado de Personal</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>DNI</th>
                            <th>Nombres</th>
                            <th>Apellido Paterno</th>
                            <th>Apellido Materno</th>
                            <th>Dirección</th>
                            <th>Sexo</th>
                            <th>Fecha Nac.</th>
                            <th>Teléfono</th>
                            <th>ID Contacto Emergencia</th>
                            <th>Correo</th>
                            <th>Grado Instrucción</th>
                            <th>Cod. Empleado</th>
                            <th>Condición Grupo Riesgo</th>
                            <th>Cargo</th>
                            <th>Salario</th>
                            <th>Modalidad</th>
                            <th>ID Sección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($empleados)): ?>
                            <tr><td colspan="19">No hay personal registrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($empleados as $empleado): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($empleado['idPersona']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['DNI']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['Nombres']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['Apellido_Paterno']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['Apellido_Materno']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['Direccion']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['Sexo']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['FechaNac']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['telefono']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['contactoEmergencia_idPersona']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['correo']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['gradoInstruccion']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['codEmpleado']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['CondicionGrupoRiesgo']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['Cargo']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['Salario']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['Modalidad']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['Docente_idSeccion'] ?? $empleado['Auxiliar_idSeccion']); ?></td>
                                    <td class="action-buttons">
                                        <a href="Empleado.php?edit=<?php echo htmlspecialchars($empleado['idPersona']); ?>" class="edit">Editar</a>
                                        <a href="Empleado.php?delete=<?php echo htmlspecialchars($empleado['idPersona']); ?>" class="delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este empleado?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="index.php" class="back-button">Volver al Menú Principal</a>
        </div>
    </div>
    <script>
        <?php if (!empty($trigger_error)): ?>
        alert(<?= json_encode($trigger_error) ?>);
        <?php endif; ?>
    </script>
</body>
</html>
