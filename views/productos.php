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
