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
