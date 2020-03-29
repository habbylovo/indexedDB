<?php
require_once("personaDB.php");
class PersonaAPI {
    protected $peopleDB;

    public function __construct() {
        $this->peopleDB = new PersonaDB();
    }

    public function API(){
        header('Content-Type: application/JSON');

        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                $this->peopleDB->getPeoples();
            break;
            case 'POST':
                $this->peopleDB->savePeople();
            break;
            case 'PUT':
                $this->peopleDB->updatePeople();
            break;
            case 'DELETE':
                $this->peopleDB->deletePeople();
            break;
            default:
                $this->peopleDB->response(405);
            break;
        }
    }
}
?>