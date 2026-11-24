models/conexion.php

<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli("localhost", "root", "", "tienda", 3307);

if ($conn->connect_error) {
    die("Conexion fallida: " . $conn->connect_error);
}
?>
---------------------------------------------------
models/modelo.php

<?php
class EnlacesPaginas {
    static function enlacesPaginasModelo($enlace) {
        // CAMBIAR: aqui va el nombre de CADA vista que crees.
        if ($enlace == "productos" || $enlace == "formulario" || $enlace == "ventas") {
            $modulo = "views/" . $enlace . ".php";
        } else {
            // Pagina que se abre si no hay accion o si no existe
            $modulo = "views/productos.php";
        }
        return $modulo;
    }
}
?>
-----------------------------
controllers/controller.php

<?php
require_once "models/modelo.php";

class PaginasController {

    public function getTemplate() {
        include "views/template.php";
    }

    public function enlacesPaginasController() {
        if (isset($_GET["accion"])) {
            $accion = $_GET["accion"];
        } else {
            $accion = "productos";   // CAMBIAR: pagina por defecto
        }
        $respuesta = EnlacesPaginas::enlacesPaginasModelo($accion);
        include $respuesta;
    }
}
?>
-------------------------------------------------
index.php

<?php
require_once "controllers/controller.php";
$mvc = new PaginasController();
$mvc->getTemplate();
?>
-------------------------------------------------
views/template.php

<html>
<head>
    <meta charset="UTF-8">
    <title>Tienda</title>
</head>
<body>
    <h1>Bienvenido</h1>

    <!-- MENU. CAMBIAR: un enlace por pagina; el nombre despues de accion=
         tiene que estar en la lista de models/modelo.php -->
    <a href="index.php?accion=productos">Productos</a> |
    <a href="index.php?accion=ventas">Ventas</a>
    <hr>

    <?php $this->enlacesPaginasController(); ?>
</body>
</html>
--------------------------------------------------------------
views/productos.php

<?php
include "models/conexion.php";

$buscar = isset($_GET["buscar"]) ? $_GET["buscar"] : "";

$like = "%" . $buscar . "%";

// Regla: un ? por columna, una "s" por cada ?, y un $like por cada ?
$stmt = $conn->prepare("SELECT * FROM productos WHERE nombre LIKE ? OR marca LIKE ?");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$res = $stmt->get_result();   // aqui quedan las filas encontradas
?>

<h2>Productos</h2>

<form method="get" action="index.php">
    <!-- Este campo oculto hace que se quede en esta misma pagina -->
    <input type="hidden" name="accion" value="productos">
    <input type="text" name="buscar" value="<?php echo $buscar; ?>" placeholder="Nombre o marca">
    <input type="submit" value="Filtrar">
    <a href="index.php?accion=productos">Mostrar todos</a> |
    <!-- El reporte recibe el mismo texto de busqueda, por eso sale filtrado -->
    <a href="reporte.php?tipo=productos&buscar=<?php echo urlencode($buscar); ?>" target="_blank">Reporte</a> |
    <a href="index.php?accion=formulario">Nuevo producto</a>
</form>
<br>

<table border="1">
    <tr>
        <th>Código</th>
        <th>Nombre</th>
        <th>Marca</th>
        <th>Stock</th>
        <th>Acciones</th>
    </tr>

    <?php while ($fila = $res->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $fila["codigo"]; ?></td>
            <td><?php echo $fila["nombre"]; ?></td>
            <td><?php echo $fila["marca"]; ?></td>
            <td><?php echo $fila["stock"]; ?></td>
            <td>
                <a href="index.php?accion=formulario&codigo=<?php echo $fila["codigo"]; ?>">Editar</a>
                <a href="models/delete.php?codigo=<?php echo $fila["codigo"]; ?>" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
            </td>
        </tr>
    <?php } ?>
</table>
-------------------------------------------
views/formulario.php

<?php
include "models/conexion.php";

$fila = array("codigo" => "", "nombre" => "", "marca" => "", "stock" => "");
$original = "";

if (isset($_GET["codigo"])) {
    $stmt = $conn->prepare("SELECT * FROM productos WHERE codigo=?");
    $stmt->bind_param("s", $_GET["codigo"]);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $original = $fila["codigo"];
}
?>

<h2><?php echo $original == "" ? "Nuevo producto" : "Editar producto"; ?></h2>

<form method="post" action="models/guardar.php">
    <input type="hidden" name="original" value="<?php echo $original; ?>">

    Código<br><input type="text" name="codigo" value="<?php echo $fila["codigo"]; ?>" required><br><br>
    Nombre<br><input type="text" name="nombre" value="<?php echo $fila["nombre"]; ?>" required><br><br>
    Marca<br><input type="text" name="marca" value="<?php echo $fila["marca"]; ?>" required><br><br>
    <!-- type="number" para numeros; agrega step="0.01" si lleva decimales -->
    Stock<br><input type="number" name="stock" value="<?php echo $fila["stock"]; ?>" required><br><br>

    <input type="submit" value="Guardar">
    <a href="index.php?accion=productos">Cancelar</a>
</form>
--------------------------------------
models/guardar.php

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
------------------------------------
models/delete.php

<?php
include "conexion.php";

$stmt = $conn->prepare("DELETE FROM productos WHERE codigo=?");
$stmt->bind_param("s", $_GET["codigo"]);
$stmt->execute();

header("Location: ../index.php?accion=productos");
?>
------------------------------------
views/ventas.php

<?php
include "models/conexion.php";

$cliente = isset($_GET["cliente"]) ? $_GET["cliente"] : "";
$like = "%" . $cliente . "%";

// CAMBIAR: tablas, columnas que quieres mostrar y la columna del filtro
$stmt = $conn->prepare("SELECT p.codigo, p.nombre AS producto, c.id_cliente, c.nombre AS cliente, v.fecha
                        FROM productos p, ventas v, clientes c
                        WHERE p.codigo = v.codigo AND c.id_cliente = v.id_cliente
                        AND c.nombre LIKE ?
                        ORDER BY v.fecha");
$stmt->bind_param("s", $like);
$stmt->execute();
$res = $stmt->get_result();

$productos = $conn->query("SELECT codigo, nombre FROM productos");
$clientes = $conn->query("SELECT id_cliente, nombre FROM clientes");
$clientesFiltro = $conn->query("SELECT nombre FROM clientes");
?>

<h2>Ventas</h2>

<form method="post" action="models/registrar.php">
    <b>Registrar venta:</b>

    <select name="codigo">
        <?php while ($p = $productos->fetch_assoc()) { ?>
            <option value="<?php echo $p["codigo"]; ?>"><?php echo $p["nombre"]; ?></option>
        <?php } ?>
    </select>

    <select name="id_cliente">
        <?php while ($c = $clientes->fetch_assoc()) { ?>
            <option value="<?php echo $c["id_cliente"]; ?>"><?php echo $c["nombre"]; ?></option>
        <?php } ?>
    </select>

    <input type="date" name="fecha" required>
    <input type="submit" value="Registrar">
</form>
<br>

<form method="get" action="index.php">
    <input type="hidden" name="accion" value="ventas">
    <b>Cliente:</b>
    <select name="cliente">
        <option value="">Todos</option>   <!-- vacio = %% = todos -->
        <?php while ($c = $clientesFiltro->fetch_assoc()) { ?>
            <!-- selected deja marcado el cliente que se filtro -->
            <option value="<?php echo $c["nombre"]; ?>" <?php if ($c["nombre"] == $cliente) echo "selected"; ?>><?php echo $c["nombre"]; ?></option>
        <?php } ?>
    </select>
    <input type="submit" value="Filtrar">
    <a href="reporte.php?tipo=ventas&buscar=<?php echo urlencode($cliente); ?>" target="_blank">Reporte</a> |
    <a href="reporte.php?tipo=masvendidos" target="_blank">Más vendidos</a>
</form>
<br>

<table border="1">
    <tr>
        <th>Producto</th>
        <th>Cliente</th>
        <th>Fecha</th>
        <th>Acción</th>
    </tr>
    <?php while ($fila = $res->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $fila["producto"]; ?></td>
            <td><?php echo $fila["cliente"]; ?></td>
            <td><?php echo $fila["fecha"]; ?></td>
            <!-- Para borrar una venta se mandan los 3 datos de su clave -->
            <td><a href="models/quitar.php?codigo=<?php echo $fila["codigo"]; ?>&id_cliente=<?php echo $fila["id_cliente"]; ?>&fecha=<?php echo $fila["fecha"]; ?>" onclick="return confirm('¿Eliminar esta venta?')">Eliminar</a></td>
        </tr>
    <?php } ?>
</table>
----------------------------------------
models/registrar.php

<?php
include "conexion.php";

$stmt = $conn->prepare("INSERT INTO ventas VALUES (?, ?, ?)");
$stmt->bind_param("sis", $_POST["codigo"], $_POST["id_cliente"], $_POST["fecha"]);

if (!$stmt->execute()) {
    die("Esa venta ya está registrada. <a href='../index.php?accion=ventas'>Volver</a>");
}
header("Location: ../index.php?accion=ventas");
?>
--------------------------------------------
models/quitar.php

<?php
include "conexion.php";

// Borra UNA venta. Hay que usar todas las columnas de su clave,
// si no, borraria todas las ventas de ese producto
$stmt = $conn->prepare("DELETE FROM ventas WHERE codigo=? AND id_cliente=? AND fecha=?");
$stmt->bind_param("sis", $_GET["codigo"], $_GET["id_cliente"], $_GET["fecha"]);
$stmt->execute();
header("Location: ../index.php?accion=ventas");
?>
---------------------------------------------------
reporte.php

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
