<?php
require_once("restauranteDB.php");
class RestauranteAPI {
    protected $restauranteDB;

    public function __construct() {
        $this->restauranteDB = new restauranteDB();
    }

    public function API(){
        // header('Content-Type: application/JSON');

        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                if(isset($_REQUEST['action'])){
                    if($_REQUEST['action'] == 'combos'){
                        $this->restauranteDB->getCombos();
                    } else if($_REQUEST['action'] == 'comandas'){
                        $this->restauranteDB->getComandas();
                    } else if($_REQUEST['action'] == 'autor'){
                        $this->restauranteDB->getAutor();
                    }
                }
            break;
            case 'POST':
                if(isset($_REQUEST['action'])){
                    if($_REQUEST['action'] == 'usuarios'){
                        $this->restauranteDB->getClaveUsuario();
                    } else if($_REQUEST['action'] == 'agregarorden'){
                        $this->restauranteDB->setOrden();
                    }
                }
                //$this->restauranteDB->saveCombo();
            break;
            case 'PUT':
                if(isset($_REQUEST['action'])){
                    if($_REQUEST['action'] == 'despachar'){
                        $this->restauranteDB->setDespachada();
                    }
                }
            break;
            case 'DELETE':
                $this->restauranteDB->deleteCombo();
            break;
            default:
                $this->restauranteDB->response(405);
            break;
        }
    }
}
?>