<?php
require_once("comboDB.php");
class ComboAPI {
    protected $comboDB;

    public function __construct() {
        $this->comboDB = new ComboDB();
    }

    public function API(){
        // header('Content-Type: application/JSON');

        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $this->comboDB->getCombos();
            break;
            case 'POST':
                $this->comboDB->saveCombo();
            break;
            case 'PUT':
                $this->comboDB->updateCombo();
            break;
            case 'DELETE':
                $this->comboDB->deleteCombo();
            break;
            default:
                $this->comboDB->response(405);
            break;
        }
    }
}
?>