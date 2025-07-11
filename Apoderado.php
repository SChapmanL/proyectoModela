<?php
// Include database connection file
// Make sure to update the path to your database.php file
require_once 'includes/funciones.php';

$conn = get_db_connection();

// Initialize variables for form
$id = '';
$DNI = '';
$Nombres = '';
$Apellido_Paterno = '';
$Apellido_Materno = '';
$Direccion = '';
$Telefono = '';
$Parentesco = ''; // New field for Parentesco
$Estudiante_Asignado = ''; // New field for Asignar a estudiante

// --- CRUD Operations ---

// 1. Create/Add Apoderado
if (isset($_POST['add'])) {
    // IMPORTANT: Replace 'your_table_name' with the actual name of your apoderado table
    // And replace 'column1', 'column2', etc., with your actual column names
    $DNI = $_POST['DNI'];
    $Nombres = $_POST['Nombres'];
    $Apellido_Paterno = $_POST['Apellido_Paterno'];
    $Apellido_Materno = $_POST['Apellido_Materno'];
    $Direccion = $_POST['Direccion'];
    $Telefono = $_POST['Telefono'];
    $Parentesco = $_POST['Parentesco'];
    $Estudiante_Asignado = $_POST['Estudiante_Asignado'];

    // Assuming 'Persona' table has DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, Telefono
    // You might need to adjust this SQL and bind_param based on your actual database schema for Apoderado and its relation to Persona
    $sql = "INSERT INTO Persona (DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, Telefono) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $DNI, $Nombres, $Apellido_Paterno, $Apellido_Materno, $Direccion, $Telefono);
    $stmt->execute();
    $stmt->close();

    // If you have a separate 'Apoderado' table that links to 'Persona' and stores Parentesco/Estudiante_Asignado
    // You would need to get the last inserted id from Persona and insert into Apoderado table
    // Example (adjust table/column names):
    // $last_person_id = $conn->insert_id;
    // $sql_apoderado = "INSERT INTO Apoderado (idPersona, Parentesco, idEstudiante) VALUES (?, ?, ?)";
    // $stmt_apoderado = $conn->prepare($sql_apoderado);
    // $stmt_apoderado->bind_param("isi", $last_person_id, $Parentesco, $Estudiante_Asignado);
    // $stmt_apoderado->execute();
    // $stmt_apoderado->close();

    header('Location: Apoderado.php'); // Redirect to refresh page
    exit();
}

// 2. Read/Fetch Apoderado for Update
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    // IMPORTANT: Adjust this query to fetch all necessary fields from your database
    // This example assumes 'Persona' table has these fields and 'idPersona' is the primary key
    $sql = "SELECT idPersona, DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, Telefono FROM Persona WHERE idPersona = ?";
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
        $Telefono = $row['Telefono'];
        // Fetch Parentesco and Estudiante_Asignado if they are in a separate table linked by idPersona
        // Example:
        // $sql_apoderado_details = "SELECT Parentesco, idEstudiante FROM Apoderado WHERE idPersona = ?";
        // $stmt_apoderado_details = $conn->prepare($sql_apoderado_details);
        // $stmt_apoderado_details->bind_param("i", $id);
        // $stmt_apoderado_details->execute();
        // $result_apoderado_details = $stmt_apoderado_details->get_result();
        // if ($result_apoderado_details->num_rows > 0) {
        //     $apoderado_details = $result_apoderado_details->fetch_assoc();
        //     $Parentesco = $apoderado_details['Parentesco'];
        //     $Estudiante_Asignado = $apoderado_details['idEstudiante'];
        // }
        // $stmt_apoderado_details->close();
    }
    $stmt->close();
}

// 3. Update Apoderado
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $DNI = $_POST['DNI'];
    $Nombres = $_POST['Nombres'];
    $Apellido_Paterno = $_POST['Apellido_Paterno'];
    $Apellido_Materno = $_POST['Apellido_Materno'];
    $Direccion = $_POST['Direccion'];
    $Telefono = $_POST['Telefono'];
    $Parentesco = $_POST['Parentesco'];
    $Estudiante_Asignado = $_POST['Estudiante_Asignado'];

    // Update Persona table
    $sql = "UPDATE Persona SET DNI = ?, Nombres = ?, Apellido_Paterno = ?, Apellido_Materno = ?, Direccion = ?, Telefono = ? WHERE idPersona = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssi", $DNI, $Nombres, $Apellido_Paterno, $Apellido_Materno, $Direccion, $Telefono, $id);
    $stmt->execute();
    $stmt->close();

    // Update Apoderado table if separate
    // Example:
    // $sql_apoderado_update = "UPDATE Apoderado SET Parentesco = ?, idEstudiante = ? WHERE idPersona = ?";
    // $stmt_apoderado_update = $conn->prepare($sql_apoderado_update);
    // $stmt_apoderado_update->bind_param("sii", $Parentesco, $Estudiante_Asignado, $id);
    // $stmt_apoderado_update->execute();
    // $stmt_apoderado_update->close();

    header('Location: Apoderado.php');
    exit();
}

// 4. Delete Apoderado
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Delete from Apoderado table first if it has a foreign key constraint on Persona
    // Example:
    // $sql_delete_apoderado = "DELETE FROM Apoderado WHERE idPersona = ?";
    // $stmt_delete_apoderado = $conn->prepare($sql_delete_apoderado);
    // $stmt_delete_apoderado->bind_param("i", $id);
    // $stmt_delete_apoderado->execute();
    // $stmt_delete_apoderado->close();

    // Then delete from Persona table
    $sql = "DELETE FROM Persona WHERE idPersona = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: Apoderado.php');
    exit();
}

// Fetch all Apoderados for display
// IMPORTANT: Adjust this query to join 'Persona' and 'Apoderado' tables if Parentesco/Estudiante_Asignado are in 'Apoderado'
$apoderados = [];
$sql = "SELECT idPersona, DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, Telefono FROM Persona";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $apoderados[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Apoderados</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Re-use your existing style.css -->
    <style>
        /* Add specific styles for Apoderado.php if needed */
        .container {
            max-width: 1200px; /* Increased max-width for two columns */
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex; /* Use flexbox for two columns */
            gap: 20px; /* Space between columns */
        }
        .form-section, .table-section {
            flex: 1; /* Each section takes equal width */
            padding: 10px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Two columns */
            gap: 10px;
            margin-bottom: 20px;
        }
        .form-grid input[type="text"],
        .form-grid select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box; /* Include padding in width */
        }
        .form-full-width {
            grid-column: 1 / -1; /* Span across both columns */
        }
        .form-submit-button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }
        .form-submit-button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
            font-size: 0.9em; /* Smaller font for table content */
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        .action-buttons a {
            text-decoration: none;
            padding: 5px 8px;
            border-radius: 5px;
            color: white;
            margin-right: 5px;
            font-size: 0.8em;
        }
        .action-buttons .edit {
            background-color: #ffc107;
        }
        .action-buttons .delete {
            background-color: #dc3545;
        }
        .back-button {
            display: block;
            width: fit-content;
            margin: 20px auto 0;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h2>Registrar Apoderado</h2>
            <form action="Apoderado.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <div class="form-grid">
                    <input type="text" name="DNI" placeholder="DNI" value="<?php echo htmlspecialchars($DNI); ?>" required>
                    <input type="text" name="Nombres" placeholder="Nombres" value="<?php echo htmlspecialchars($Nombres); ?>" required>
                    <input type="text" name="Apellido_Paterno" placeholder="Apellido Paterno" value="<?php echo htmlspecialchars($Apellido_Paterno); ?>" required>
                    <input type="text" name="Apellido_Materno" placeholder="Apellido Materno" value="<?php echo htmlspecialchars($Apellido_Materno); ?>" required>
                    
                    <input type="text" name="Estudiante_Asignado" placeholder="Asignar a estudiante (ID)" value="<?php echo htmlspecialchars($Estudiante_Asignado); ?>">
                    <select name="Parentesco">
                        <option value="">Registrar Parentesco</option>
                        <option value="Padre" <?php echo ($Parentesco == 'Padre') ? 'selected' : ''; ?>>Padre</option>
                        <option value="Madre" <?php echo ($Parentesco == 'Madre') ? 'selected' : ''; ?>>Madre</option>
                        <option value="Tutor" <?php echo ($Parentesco == 'Tutor') ? 'selected' : ''; ?>>Tutor</option>
                        <option value="Otro" <?php echo ($Parentesco == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>

                    <input type="text" name="Direccion" placeholder="Dirección" value="<?php echo htmlspecialchars($Direccion); ?>" class="form-full-width" required>
                    <input type="text" name="Telefono" placeholder="Teléfono" value="<?php echo htmlspecialchars($Telefono); ?>" class="form-full-width" required>
                </div>
                <?php if ($id): ?>
                    <input type="submit" name="update" value="Actualizar Apoderado" class="form-submit-button">
                <?php else: ?>
                    <input type="submit" name="add" value="Enviar" class="form-submit-button">
                <?php endif; ?>
            </form>
        </div>

        <div class="table-section">
            <h2>Listado de Apoderados</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>DNI</th>
                        <th>Nombres</th>
                        <th>Apellido Paterno</th>
                        <th>Apellido Materno</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Parentesco</th>
                        <th>Estudiante Asignado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($apoderados)): ?>
                        <tr><td colspan="10">No hay apoderados registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($apoderados as $apoderado): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($apoderado['idPersona']); ?></td>
                                <td><?php echo htmlspecialchars($apoderado['DNI']); ?></td>
                                <td><?php echo htmlspecialchars($apoderado['Nombres']); ?></td>
                                <td><?php echo htmlspecialchars($apoderado['Apellido_Paterno']); ?></td>
                                <td><?php echo htmlspecialchars($apoderado['Apellido_Materno']); ?></td>
                                <td><?php echo htmlspecialchars($apoderado['Direccion']); ?></td>
                                <td><?php echo htmlspecialchars($apoderado['Telefono']); ?></td>
                                <!-- IMPORTANT: If Parentesco and Estudiante Asignado are in a separate table, you'll need to join them in the SQL query above -->
                                <td><?php echo htmlspecialchars($apoderado['Parentesco'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($apoderado['Estudiante_Asignado'] ?? 'N/A'); ?></td>
                                <td class="action-buttons">
                                    <a href="Apoderado.php?edit=<?php echo htmlspecialchars($apoderado['idPersona']); ?>" class="edit">Editar</a>
                                    <a href="Apoderado.php?delete=<?php echo htmlspecialchars($apoderado['idPersona']); ?>" class="delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este apoderado?');">Eliminar</a>
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