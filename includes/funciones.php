<?php

function get_db_connection() {
    try {
        //importar las credenciales
        require 'database.php';
        return $db;
    } catch (\Throwable $th) {
        var_dump($th);
        exit(); // Exit if connection fails
    }
}

// The existing functions might need to be refactored to use get_db_connection() if they are still needed.
// For now, I'm just adding the new function.

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
    // Try-catch: ejecuta todas las lineas de codigo dentro del try, si hay un error en alguna, el bloque catch indica donde está el error
    try {
        // Importar credenciales
        require 'database.php';

        // Consulta SQL
        $sql = "SELECT * FROM persona";

        // Realizar consulta
        $query = mysqli_query($db, $sql);
        return $query;        
        //Acceder a resultados
        // echo '<pre>';
        // var_dump( mysqli_fetch_assoc($query) );
        // echo '</pre>';
        // Cerrar conexión
        $resultado = mysqli_close($db);
    } catch (\Throwable $th) {
        var_dump($th);
        //throw $th;
    }
}


// obtener_servicios(); // This line should probably be removed or called conditionally if it's not meant to run on every include.
?>