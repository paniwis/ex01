<?php
include "conexion.php";

// Borra UNA venta. Hay que usar todas las columnas de su clave,
// si no, borraria todas las ventas de ese producto
$stmt = $conn->prepare("DELETE FROM ventas WHERE codigo=? AND id_cliente=? AND fecha=?");
$stmt->bind_param("sis", $_GET["codigo"], $_GET["id_cliente"], $_GET["fecha"]);
$stmt->execute();
header("Location: ../index.php?accion=ventas");
?>
