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
