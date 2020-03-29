<?php
require_once("comandaDB.php");
class ComandaAPI {
    protected $comandaDB;

    public function __construct() {
        $this->comandaDB = new ComandaDB();
    }

    public function API(){
        // header('Content-Type: application/JSON');

        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $this->comandaDB->getComandas();
            break;
            case 'POST':
                $this->comandaDB->saveComanda();
            break;
            case 'DELETE':
                $this->comandaDB->deleteComanda();
            break;
            default:
                $this->comandaDB->response(405);
            break;
        }
    }
}
?>