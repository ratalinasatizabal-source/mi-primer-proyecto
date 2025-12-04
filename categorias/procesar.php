<?php
include("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $color_hex = $_POST['color_hex'];

    // CREAR
    if ($accion === "crear") {
        $sql = "INSERT INTO categorias (nombre, descripcion, color_hex) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sss", $nombre, $descripcion, $color_hex);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        }
    }

    // EDITAR
    if ($accion === "editar") {
        $id = $_POST['id'];
        $sql = "UPDATE categorias SET nombre=?, descripcion=?, color_hex=? WHERE id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $descripcion, $color_hex, $id);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        }
    }

    echo "Error al procesar";
}
