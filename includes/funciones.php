<?php

function get_db_connection() {
    try {
        //importar las credenciales
        require 'database.php';
        return $db;
    } catch (\Throwable $th) {
        var_dump($th);
        exit();
    }
}

function obtener_servicios() {
    try {
        //importar las credenciales
        require 'database.php';
        // si esta no se carga, no ejecuta

        //consulta sql
        $sql = 'SELECT * FROM Persona;';
        //realizar la consulta
        $consulta_personas = mysqli_query($db, $sql);

        //acceder a los resultados
        return $consulta_personas;
        //cerrar conexion
        $resultado = mysqli_close($db);
    } catch (\Throwable $th) {
        //throw $th;
        var_dump($th);
    }

}

function obtener_apoderados() {
    try {
        // Importar credenciales
        require 'database.php';

        // Consulta SQL
        $sql = "SELECT * FROM persona";

        // Realizar consulta
        $query = mysqli_query($db, $sql);
        return $query;
        $resultado = mysqli_close($db);
    } catch (\Throwable $th) {
        var_dump($th);
        //throw $th;
    }
}
?>