<?php
include "conexion.php";

$stmt = $conn->prepare("INSERT INTO ventas VALUES (?, ?, ?)");
$stmt->bind_param("sis", $_POST["codigo"], $_POST["id_cliente"], $_POST["fecha"]);

if (!$stmt->execute()) {
    die("Esa venta ya está registrada. <a href='../index.php?accion=ventas'>Volver</a>");
}
header("Location: ../index.php?accion=ventas");
?>
