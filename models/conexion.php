<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli("localhost", "root", "", "tienda", 3307);

if ($conn->connect_error) {
    die("Conexion fallida: " . $conn->connect_error);
}
?>
