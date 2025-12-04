<?php
$host = 'localhost';
$usuario = 'root';
$password = '';
$basedatos = 'crud_basico';

$conexion = new mysqli($host, $usuario, $password, $basedatos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
    else{
echo"";
}
?>