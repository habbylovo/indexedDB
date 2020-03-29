<?php
require_once("db/conexion.php");
class PersonaDB {
    protected $dbConn;
    protected $mysqliconn;

    public function __construct() {
        try{
            $this->mysqliconn = baseDatos::conectar();
        }catch (mysqli_sql_exception $e){
            http_response_code(500);
            exit;
        }
    }

    /**
     * Obtiene un solo registro dado su ID
     * @param int $id identificador unico de registro
     * @return Array array con los registros obtenidos de la base de datos
     */
    public function getPeople($id=0){
        $stmt = $this->mysqliconn->prepare("SELECT * FROM persona WHERE idPersona=? ; ");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $peoples = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $peoples;
    }

    /**
     * obtiene todos los registros de la tabla "personas"
     * @return Array array con los registros obtenidos de la base de datos
     */
    public function getPeoples2(){
        $result = $this->mysqliconn->query('SELECT * FROM comanda');
        $peoples = $result->fetch_all(MYSQLI_ASSOC);
        $result->close();
        return $peoples;
    }

    function getPeoples(){
        if($_REQUEST['action']=='personas'){
            $db = new PersonaDB();
            if(isset($_REQUEST['id'])){
                $response = $db->getPeople($_REQUEST['id']);
                echo json_encode($response,JSON_PRETTY_PRINT);
            } else{
                $response = $db->getPeoples2();
                echo json_encode($response,JSON_PRETTY_PRINT);
            }
        } else{
            $this->response(400);
        }
    }

    /**
     * añade un nuevo registro en la tabla persona
     * @param String $name nombre completo de persona
     * @return bool TRUE|FALSE
     */
    public function insert($name=''){
        $stmt = $this->mysqliconn->prepare("INSERT INTO persona(nombre) VALUES (?); ");
        $stmt->bind_param('s', $name);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    /**
     * metodo para guardar un nuevo registro de persona en la base de datos
     */
    function savePeople(){
        if($_REQUEST['action']=='personas'){
            $obj = json_decode(file_get_contents('php://input'));
            $objArr = (array)$obj;
            if (empty($objArr)){
                $this->response(422,"error","Nada que anadir. Comprobar json");
            }else if(isset($obj[0]->name)){
                $people = new PersonaDB();
                $people->insert($obj[0]->name);
                $this->response(200,"success","Nueva persona agregada");
            }else{
                $this->response(422,"error","La propiedad no esta definida");
            }
        } else{
            $this->response(400);
        }
    }
   
    /**
    * elimina un registro dado el ID
    * @param int $id Identificador unico de registro
    * @return Bool TRUE|FALSE
    */
    public function delete($id=0) {
        $stmt = $this->mysqliconn->prepare("DELETE FROM persona WHERE idPersona = ? ; ");
        $stmt->bind_param('s', $id);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    /**
     * elimina persona
     */
    function deletePeople(){
        if( isset($_REQUEST['action']) && isset($_REQUEST['id']) ){
            if($_REQUEST['action']=='personas'){
                $db = new PersonaDB();
                $db->delete($_REQUEST['id']);
                $this->response(204);
                exit;
            }
        }
        $this->response(400);
    }

    /**
     * Actualiza registro dado su ID
     * @param int $id Description
     */
    public function update($id, $newName) {
        if($this->checkID($id)){
            $stmt = $this->mysqliconn->prepare("UPDATE persona SET nombre=? WHERE idPersona = ? ; ");
            $stmt->bind_param('ss', $newName,$id);
            $r = $stmt->execute();
            $stmt->close();
            return $r;
        }
        return false;
    }

    /**
     * Actualiza un recurso
     */
    function updatePeople() {
        if( isset($_REQUEST['action']) && isset($_REQUEST['id']) ){
            if($_REQUEST['action']=='personas'){
                $obj = json_decode( file_get_contents('php://input') );
                $objArr = (array)$obj;
                if (empty($objArr)){
                    $this->response(422,"error","Nada que anadir. Comprobar json");
                } else if(isset($obj[0]->name)){
                    $db = new PersonaDB();
                    $db->update($_REQUEST['id'], $obj[0]->name);
                    $this->response(200,"success","Persona actualizada");
                }else{
                    $this->response(422,"error","La propiedad no esta definida");
                }
                exit;
            }
        }
        $this->response(400);
    }

    /**
    * verifica si un ID existe
    * @param int $id Identificador unico de registro
    * @return Bool TRUE|FALSE
    */
    public function checkID($id){
        $stmt = $this->mysqliconn->prepare("SELECT * FROM persona WHERE idPersona=?");
        $stmt->bind_param("s", $id);
        if($stmt->execute()){
            $stmt->store_result();
            if ($stmt->num_rows == 1){
                return true;
            }
        }
        return false;
    }

    /**
    * Respuesta al cliente
    * @param int $code Codigo de respuesta HTTP
    * @param String $status indica el estado de la respuesta puede ser "success" o "error"
    * @param String $message Descripcion de lo ocurrido
    */
    function response($code=200, $status="", $message="") {
        http_response_code($code);
        if( !empty($status) && !empty($message) ){
            $response = array("status" => $status ,"message"=>$message);
            echo json_encode($response,JSON_PRETTY_PRINT);
        }
    } 

}
?>