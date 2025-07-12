<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main-container">
        <div class="top-bar">
            <h1>Menu principal</h1>
            <button id="logout-button" class="logout-button">Cerrar Sesión</button>
        </div>
        <div class="button-container">
            <a href="RegistroPersonas.php" class="menu-button" id="btn-gestion-alumno">Gestion de alumno</a>
            <a href="Apoderado.php" class="menu-button" id="btn-gestion-apoderado">Gestion de apoderado</a>
            <a href="Empleado.php" class="menu-button" id="btn-gestion-personal">Gestion de personal</a>
            <a href="RegistroAsistencia.php" class="menu-button" id="btn-asistencia">Asistencia</a>
            <a href="Matricula.php" class="menu-button" id="btn-matricula">Matricula</a>
            <a href="RegistroNotas.php" class="menu-button" id="btn-notas">Notas</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userRole = sessionStorage.getItem('userRole');

            if (!userRole) {
                window.location.href = 'login.html';
                return;
            }

            if (userRole === 'Profesora1') {
                document.getElementById('btn-gestion-alumno').style.display = 'none';
                document.getElementById('btn-gestion-apoderado').style.display = 'none';
                document.getElementById('btn-gestion-personal').style.display = 'none';
                document.getElementById('btn-matricula').style.display = 'none';
            }

            document.getElementById('logout-button').addEventListener('click', function() {
                sessionStorage.removeItem('userRole');
                window.location.href = 'login.html';
            });
        });
    </script>
</body>
</html>
