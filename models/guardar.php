<?php
include "conexion.php";

// Regla: una letra por cada ? y en el MISMO orden que los ?

if ($_POST["original"] == "") {
    $stmt = $conn->prepare("INSERT INTO productos VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $_POST["codigo"], $_POST["nombre"], $_POST["marca"], $_POST["stock"]);
} else {
    $stmt = $conn->prepare("UPDATE productos SET codigo=?, nombre=?, marca=?, stock=? WHERE codigo=?");
    $stmt->bind_param("sssis", $_POST["codigo"], $_POST["nombre"], $_POST["marca"], $_POST["stock"], $_POST["original"]);
}

if (!$stmt->execute()) {
    die("Error al guardar: " . $conn->error . " <a href='../index.php?accion=productos'>Volver</a>");
}

header("Location: ../index.php?accion=productos");
?>
