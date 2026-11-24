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
