<?php
require_once 'includes/funciones.php';

// Inicializar variables
$error = '';
$success = '';
$trigger_error = ''; // Variable para el error del trigger
$nombres = '';
$apellido_paterno = '';
$apellido_materno = '';
$dni = '';
$direccion = '';
$codAlumno = '';
$contacto_emergencia = '';

// Conexión a la base de datos
$conn = get_db_connection();

// ... (resto del código PHP sin cambios hasta el bloque catch)

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
        // ... (código de validación y preparación sin cambios)

        // Iniciar transacción
        $conn->begin_transaction();

        // Insertar nueva persona (sin teléfono)
        $sql_persona = "INSERT INTO persona (DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, contactoEmergencia_idPersona) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_persona = $conn->prepare($sql_persona);
        $stmt_persona->bind_param("sssssi", $dni, $nombres, $apellido_paterno, $apellido_materno, $direccion, $contacto_emergencia);
        
        if (!$stmt_persona->execute()) {
            // Esto lanzará una excepción que será capturada por el bloque catch
            throw new mysqli_sql_exception($conn->error, $conn->errno);
        }

        // ... (resto del código de inserción)

        $conn->commit();
        $success = "Estudiante registrado exitosamente con código: " . htmlspecialchars($codAlumno);
        
        // Limpiar formulario después de registro exitoso
        $nombres = $apellido_paterno = $apellido_materno = $dni = $direccion = $contacto_emergencia = '';
        $codAlumno = generarCodigoAlumno($conn);

    } catch (Exception $e) {
        $conn->rollback();
        if (strpos($e->getMessage(), 'control_horario_persona') !== false) {
            $trigger_error = $e->getMessage();
        } else {
            $error = $e->getMessage();
        }
    }
}

// ... (resto del código PHP sin cambios)
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- ... (head sin cambios) -->
</head>
<body>
    <!-- ... (cuerpo del HTML sin cambios hasta el final) -->
    </div>

    <script>
        <?php if (!empty($trigger_error)): ?>
        alert(<?= json_encode($trigger_error) ?>);
        <?php endif; ?>
    </script>
</body>
</html>