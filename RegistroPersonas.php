<?php
require_once 'includes/funciones.php';
$conn = get_db_connection();

if (isset($_POST['add_persona'])) {
    $conn->begin_transaction();
    try {
        // Datos del Estudiante (Persona)
        $dni_estudiante = $_POST['dni_estudiante'];
        $nombres_estudiante = $_POST['nombres_estudiante'];
        $apellido_paterno_estudiante = $_POST['apellido_paterno_estudiante'];
        $apellido_materno_estudiante = $_POST['apellido_materno_estudiante'];
        $direccion_estudiante = $_POST['direccion_estudiante'];
        $sexo_estudiante = $_POST['sexo_estudiante'];
        $fecha_nac_estudiante = $_POST['fecha_nac_estudiante'];
        $cod_alumno = $_POST['cod_alumno'];

        // Insertar en Persona (Estudiante)
        $stmt = $conn->prepare("INSERT INTO Persona (DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, Sexo, FechaNac, contactoEmergencia_idPersona) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $contactoEmergencia_idPersona_estudiante = null; // O un ID válido si ya existe
        $stmt->bind_param("sssssssi", $dni_estudiante, $nombres_estudiante, $apellido_paterno_estudiante, $apellido_materno_estudiante, $direccion_estudiante, $sexo_estudiante, $fecha_nac_estudiante, $contactoEmergencia_idPersona_estudiante);
        $stmt->execute();
        $idPersona_estudiante = $conn->insert_id;
        $stmt->close();

        // Insertar en Estudiante
        $stmt = $conn->prepare("INSERT INTO Estudiante (codAlumno, idPersona) VALUES (?, ?)");
        $stmt->bind_param("si", $cod_alumno, $idPersona_estudiante);
        $stmt->execute();
        $stmt->close();

        // Datos del Apoderado 1
        $dni_ppff1 = $_POST['dni_ppff1'];
        $nombres_ppff1 = $_POST['nombres_ppff1'];
        $apellido_paterno_ppff1 = $_POST['apellido_paterno_ppff1'];
        $apellido_materno_ppff1 = $_POST['apellido_materno_ppff1'];
        $direccion_ppff1 = $_POST['direccion_ppff1'];
        $sexo_ppff1 = $_POST['sexo_ppff1'];
        $fecha_nac_ppff1 = $_POST['fecha_nac_ppff1'];
        $telefono_ppff1 = $_POST['telefono_ppff1'];
        $correo_ppff1 = $_POST['correo_ppff1'];
        $grado_instruccion_ppff1 = $_POST['grado_instruccion_ppff1'];
        $vive_ppff1 = $_POST['vive_ppff1'];
        $ocupacion_ppff1 = $_POST['ocupacion_ppff1'];
        $parentesco_ppff1 = $_POST['parentesco_ppff1'];
        $vive_con_estudiante_ppff1 = $_POST['vive_con_estudiante_ppff1'];

        // Insertar en Persona (Apoderado 1)
        $stmt = $conn->prepare("INSERT INTO Persona (DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, Sexo, FechaNac, telefono, contactoEmergencia_idPersona) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $contactoEmergencia_idPersona_ppff1 = null; // O un ID válido
        $stmt->bind_param("ssssssssi", $dni_ppff1, $nombres_ppff1, $apellido_paterno_ppff1, $apellido_materno_ppff1, $direccion_ppff1, $sexo_ppff1, $fecha_nac_ppff1, $telefono_ppff1, $contactoEmergencia_idPersona_ppff1);
        $stmt->execute();
        $idPersona_ppff1 = $conn->insert_id;
        $stmt->close();

        // Insertar en No_Estudiante (Apoderado 1)
        $stmt = $conn->prepare("INSERT INTO No_Estudiante (correo, gradoInstruccion, idPersona) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $correo_ppff1, $grado_instruccion_ppff1, $idPersona_ppff1);
        $stmt->execute();
        $stmt->close();

        // Insertar en PPFF (Apoderado 1)
        $stmt = $conn->prepare("INSERT INTO PPFF (Vive, Ocupacion, idPersona) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $vive_ppff1, $ocupacion_ppff1, $idPersona_ppff1);
        $stmt->execute();
        $stmt->close();

        // Insertar en apoderado (Apoderado 1)
        $stmt = $conn->prepare("INSERT INTO apoderado (Parentesco, viveConEstudiante, idPersonaEst, idPersonaPPFF) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $parentesco_ppff1, $vive_con_estudiante_ppff1, $idPersona_estudiante, $idPersona_ppff1);
        $stmt->execute();
        $stmt->close();

        // Datos del Apoderado 2 (Opcional)
        if (isset($_POST['dni_ppff2']) && !empty($_POST['dni_ppff2'])) {
            $dni_ppff2 = $_POST['dni_ppff2'];
            $nombres_ppff2 = $_POST['nombres_ppff2'];
            $apellido_paterno_ppff2 = $_POST['apellido_paterno_ppff2'];
            $apellido_materno_ppff2 = $_POST['apellido_materno_ppff2'];
            $direccion_ppff2 = $_POST['direccion_ppff2'];
            $sexo_ppff2 = $_POST['sexo_ppff2'];
            $fecha_nac_ppff2 = $_POST['fecha_nac_ppff2'];
            $telefono_ppff2 = $_POST['telefono_ppff2'];
            $correo_ppff2 = $_POST['correo_ppff2'];
            $grado_instruccion_ppff2 = $_POST['grado_instruccion_ppff2'];
            $vive_ppff2 = $_POST['vive_ppff2'];
            $ocupacion_ppff2 = $_POST['ocupacion_ppff2'];
            $parentesco_ppff2 = $_POST['parentesco_ppff2'];
            $vive_con_estudiante_ppff2 = $_POST['vive_con_estudiante_ppff2'];

            // Insertar en Persona (Apoderado 2)
            $stmt = $conn->prepare("INSERT INTO Persona (DNI, Nombres, Apellido_Paterno, Apellido_Materno, Direccion, Sexo, FechaNac, telefono, contactoEmergencia_idPersona) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $contactoEmergencia_idPersona_ppff2 = null; // O un ID válido
            $stmt->bind_param("ssssssssi", $dni_ppff2, $nombres_ppff2, $apellido_paterno_ppff2, $apellido_materno_ppff2, $direccion_ppff2, $sexo_ppff2, $fecha_nac_ppff2, $telefono_ppff2, $contactoEmergencia_idPersona_ppff2);
            $stmt->execute();
            $idPersona_ppff2 = $conn->insert_id;
            $stmt->close();

            // Insertar en No_Estudiante (Apoderado 2)
            $stmt = $conn->prepare("INSERT INTO No_Estudiante (correo, gradoInstruccion, idPersona) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $correo_ppff2, $grado_instruccion_ppff2, $idPersona_ppff2);
            $stmt->execute();
            $stmt->close();

            // Insertar en PPFF (Apoderado 2)
            $stmt = $conn->prepare("INSERT INTO PPFF (Vive, Ocupacion, idPersona) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $vive_ppff2, $ocupacion_ppff2, $idPersona_ppff2);
            $stmt->execute();
            $stmt->close();

            // Insertar en apoderado (Apoderado 2)
            $stmt = $conn->prepare("INSERT INTO apoderado (Parentesco, viveConEstudiante, idPersonaEst, idPersonaPPFF) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $parentesco_ppff2, $vive_con_estudiante_ppff2, $idPersona_estudiante, $idPersona_ppff2);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        header("Location: RegistroPersonas.php?success=true");
        exit();
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo "Error al registrar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Personas</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef1f5;
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
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
        .ppff-section {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .add-ppff-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .add-ppff-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registro de Estudiantes y Apoderados</h2>
        <form action="RegistroPersonas.php" method="POST">
            <h3>Datos del Estudiante</h3>
            <div class="form-grid">
                <input type="text" name="dni_estudiante" placeholder="DNI Estudiante" required>
                <input type="text" name="nombres_estudiante" placeholder="Nombres Estudiante" required>
                <input type="text" name="apellido_paterno_estudiante" placeholder="Apellido Paterno Estudiante" required>
                <input type="text" name="apellido_materno_estudiante" placeholder="Apellido Materno Estudiante" required>
                <input type="text" name="direccion_estudiante" placeholder="Dirección Estudiante" required>
                <select name="sexo_estudiante" required>
                    <option value="">Seleccione Sexo</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>
                <input type="date" name="fecha_nac_estudiante" placeholder="Fecha Nacimiento Estudiante" required>
                <input type="text" name="cod_alumno" placeholder="Código Alumno" required>
            </div>

            <h3>Datos del Apoderado 1</h3>
            <div class="ppff-section">
                <div class="form-grid">
                    <input type="text" name="dni_ppff1" placeholder="DNI Apoderado 1" required>
                    <input type="text" name="nombres_ppff1" placeholder="Nombres Apoderado 1" required>
                    <input type="text" name="apellido_paterno_ppff1" placeholder="Apellido Paterno Apoderado 1" required>
                    <input type="text" name="apellido_materno_ppff1" placeholder="Apellido Materno Apoderado 1" required>
                    <input type="text" name="direccion_ppff1" placeholder="Dirección Apoderado 1" required>
                    <select name="sexo_ppff1" required>
                        <option value="">Seleccione Sexo</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                    </select>
                    <input type="date" name="fecha_nac_ppff1" placeholder="Fecha Nacimiento Apoderado 1" required>
                    <input type="text" name="telefono_ppff1" placeholder="Teléfono Apoderado 1" required>
                    <input type="email" name="correo_ppff1" placeholder="Correo Apoderado 1" required>
                    <input type="text" name="grado_instruccion_ppff1" placeholder="Grado Instrucción Apoderado 1" required>
                    <select name="vive_ppff1" required>
                        <option value="">¿Vive?</option>
                        <option value="Si">Sí</option>
                        <option value="No">No</option>
                    </select>
                    <input type="text" name="ocupacion_ppff1" placeholder="Ocupación Apoderado 1" required>
                    <input type="text" name="parentesco_ppff1" placeholder="Parentesco con Estudiante" required>
                    <select name="vive_con_estudiante_ppff1" required>
                        <option value="">¿Vive con Estudiante?</option>
                        <option value="Si">Sí</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>

            <button type="button" class="add-ppff-button" id="addPpffButton">Agregar otro Apoderado</button>

            <div id="ppff2Section" class="ppff-section" style="display: none;">
                <h3>Datos del Apoderado 2 (Opcional)</h3>
                <div class="form-grid">
                    <input type="text" name="dni_ppff2" placeholder="DNI Apoderado 2">
                    <input type="text" name="nombres_ppff2" placeholder="Nombres Apoderado 2">
                    <input type="text" name="apellido_paterno_ppff2" placeholder="Apellido Paterno Apoderado 2">
                    <input type="text" name="apellido_materno_ppff2" placeholder="Apellido Materno Apoderado 2">
                    <input type="text" name="direccion_ppff2" placeholder="Dirección Apoderado 2">
                    <select name="sexo_ppff2">
                        <option value="">Seleccione Sexo</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                    </select>
                    <input type="date" name="fecha_nac_ppff2" placeholder="Fecha Nacimiento Apoderado 2">
                    <input type="text" name="telefono_ppff2" placeholder="Teléfono Apoderado 2">
                    <input type="email" name="correo_ppff2" placeholder="Correo Apoderado 2">
                    <input type="text" name="grado_instruccion_ppff2" placeholder="Grado Instrucción Apoderado 2">
                    <select name="vive_ppff2">
                        <option value="">¿Vive?</option>
                        <option value="Si">Sí</option>
                        <option value="No">No</option>
                    </select>
                    <input type="text" name="ocupacion_ppff2" placeholder="Ocupación Apoderado 2">
                    <input type="text" name="parentesco_ppff2" placeholder="Parentesco con Estudiante">
                    <select name="vive_con_estudiante_ppff2">
                        <option value="">¿Vive con Estudiante?</option>
                        <option value="Si">Sí</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>

            <input type="submit" name="add_persona" value="Registrar Persona" class="form-submit-button">
        </form>
        <a href="index.php" class="back-button">Volver al Menú Principal</a>
    </div>

    <script>
        document.getElementById('addPpffButton').addEventListener('click', function() {
            var ppff2Section = document.getElementById('ppff2Section');
            if (ppff2Section.style.display === 'none') {
                ppff2Section.style.display = 'block';
                this.textContent = 'Ocultar Apoderado 2';
                // Hacer los campos requeridos si se muestran
                ppff2Section.querySelectorAll('input, select').forEach(function(element) {
                    if (element.name !== 'dni_ppff2' && element.name !== 'nombres_ppff2' && element.name !== 'apellido_paterno_ppff2' && element.name !== 'apellido_materno_ppff2' && element.name !== 'direccion_ppff2' && element.name !== 'sexo_ppff2' && element.name !== 'fecha_nac_ppff2' && element.name !== 'telefono_ppff2' && element.name !== 'correo_ppff2' && element.name !== 'grado_instruccion_ppff2' && element.name !== 'vive_ppff2' && element.name !== 'ocupacion_ppff2' && element.name !== 'parentesco_ppff2' && element.name !== 'vive_con_estudiante_ppff2') {
                        element.required = true;
                    }
                });
            } else {
                ppff2Section.style.display = 'none';
                this.textContent = 'Agregar otro Apoderado';
                // Quitar el atributo required si se ocultan
                ppff2Section.querySelectorAll('input, select').forEach(function(element) {
                    element.required = false;
                    element.value = ''; // Limpiar valores al ocultar
                });
            }
        });
    </script>
</body>
</html>
