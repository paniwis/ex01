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
