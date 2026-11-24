<?php
include "fpdf/fpdf.php";
include "models/conexion.php";

function texto($valor) {
    return iconv("UTF-8", "windows-1252//TRANSLIT", (string)$valor);
}

function generarPDF($titulo, $encabezados, $anchos, $campos, $res) {
    $pdf = new FPDF();
    $pdf->AddPage();

    // Titulo y fecha de hoy
    $pdf->SetFont("Arial", "B", 14);
    $pdf->Cell(190, 10, texto($titulo), 0, 1, "C");
    $pdf->SetFont("Arial", "", 10);
    $pdf->Cell(190, 8, "Fecha: " . date("d/m/Y"), 0, 1, "C");
    $pdf->Ln(3);

    // Encabezados
    $pdf->SetFont("Arial", "B", 10);
    for ($i = 0; $i < count($encabezados); $i++) {
        $salto = ($i == count($encabezados) - 1) ? 1 : 0;
        $pdf->Cell($anchos[$i], 8, texto($encabezados[$i]), 1, $salto, "C");
    }

    // Filas
    $pdf->SetFont("Arial", "", 10);
    while ($fila = $res->fetch_assoc()) {
        for ($i = 0; $i < count($campos); $i++) {
            $salto = ($i == count($campos) - 1) ? 1 : 0;
            $pdf->Cell($anchos[$i], 8, texto($fila[$campos[$i]]), 1, $salto, "C");
        }
    }

    // Total
    $pdf->Ln(3);
    $pdf->Cell(190, 8, "Total de registros: " . $res->num_rows, 0, 1, "L");
    $pdf->Output("I", "reporte.pdf");
}

$tipo = isset($_GET["tipo"]) ? $_GET["tipo"] : "productos";
$buscar = isset($_GET["buscar"]) ? $_GET["buscar"] : "";
$like = "%" . $buscar . "%";

if ($tipo == "productos") {
    $stmt = $conn->prepare("SELECT * FROM productos WHERE nombre LIKE ? OR marca LIKE ?");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    generarPDF("Reporte de productos",
               array("CÓDIGO", "NOMBRE", "MARCA", "STOCK"),   // titulos
               array(30, 70, 60, 30),                         // anchos: suman 190
               array("codigo", "nombre", "marca", "stock"),   // columnas de la base
               $stmt->get_result());
}

if ($tipo == "ventas") {
    $stmt = $conn->prepare("SELECT p.nombre AS producto, c.nombre AS cliente, v.fecha
                            FROM productos p, ventas v, clientes c
                            WHERE p.codigo = v.codigo AND c.id_cliente = v.id_cliente
                            AND c.nombre LIKE ?
                            ORDER BY v.fecha");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    generarPDF("Reporte de ventas",
               array("PRODUCTO", "CLIENTE", "FECHA"),
               array(80, 70, 40),
               array("producto", "cliente", "fecha"),
               $stmt->get_result());
}

if ($tipo == "masvendidos") {
    $stmt = $conn->prepare("SELECT p.nombre AS producto, COUNT(*) AS veces
                            FROM productos p, ventas v
                            WHERE p.codigo = v.codigo
                            GROUP BY p.codigo, p.nombre
                            ORDER BY veces DESC");
    $stmt->execute();
    generarPDF("Productos más vendidos",
               array("PRODUCTO", "VECES VENDIDO"),
               array(140, 50),
               array("producto", "veces"),
               $stmt->get_result());
}
?>
