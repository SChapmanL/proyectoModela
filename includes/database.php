<?php

$db = mysqli_connect('localhost', 'root', '', 'appcaminemos');

if (!$db) {
    echo('Conexion exitosa');
    exit;
}
?>