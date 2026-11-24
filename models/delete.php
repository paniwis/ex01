<?php
include "conexion.php";

$stmt = $conn->prepare("DELETE FROM productos WHERE codigo=?");
$stmt->bind_param("s", $_GET["codigo"]);
$stmt->execute();

header("Location: ../index.php?accion=productos");
?>
