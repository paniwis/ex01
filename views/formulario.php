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
