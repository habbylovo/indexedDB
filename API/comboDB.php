<?php
require_once("db/conexion.php");
class ComboDB {
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
    public function getCombo($id=0){
        $stmt = $this->mysqliconn->prepare("SELECT idCombo, nombre, descripcion, precio, imagen FROM combo WHERE idCombo=? ; ");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->bind_result($col1, $col2, $col3, $col4, $col5);
        $combos = array();
        while ($stmt->fetch()) {
            $combos[] = ['idCombo'=>$col1, 'nombre'=>$col2, 'descripcion'=>$col3, 'precio'=>$col4, 'imagen'=>$col5];
        }
        $stmt->close();
        return $combos;
    }

    /**
     * obtiene todos los registros de la tabla "personas"
     * @return Array array con los registros obtenidos de la base de datos
     */
    public function getCombos2(){
        $result = $this->mysqliconn->query('SELECT * FROM combo');
        $combos = array();
        while ($row = $result->fetch_assoc()) {
            $combos[] = $row;
        }
        $result->close();
        return $combos;
    }

    function getCombos(){
        if($_REQUEST['action'] == 'combos'){
            $db = new ComboDB();
            if(isset($_REQUEST['id'])){
                $response = $db->getCombo($_REQUEST['id']);
                echo json_encode($response,JSON_FORCE_OBJECT);
            } else{
                $response = $db->getCombos2();
                $respuesta_json = json_encode($response) ;
                echo $respuesta_json;
            }
        } else{
            $this->response(400);
        }
    }

    /**
     * añade un nuevo registro en la tabla combo
     * @param String $name nombre completo de persona
     * @return bool TRUE|FALSE
     */
    public function insert($nombre, $descripcion, $estado, $precio, $imagen){
        $stmt = $this->mysqliconn->prepare("INSERT INTO combo(nombre, descripcion, estado, precio, imagen) VALUES (?, ?, ?, ?, ?); ");
        $stmt->bind_param('sssds', $nombre, $descripcion, $estado, $precio, $imagen);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    /**
     * metodo para guardar un nuevo registro de persona en la base de datos
     */
    function savePeople(){
        if($_REQUEST['action']=='combos'){
            $obj = json_decode(file_get_contents('php://input'));
            $objArr = (array)$obj;
            if (empty($objArr)){
                $this->response(422,"error","Nada que anadir. Comprobar json");
            }else if(isset($obj[0]->nombre) && isset($obj[0]->descripcion) && isset($obj[0]->estado) && isset($obj[0]->precio) && isset($obj[0]->imagen)){
                $combo = new ComboDB();
                $combo->insert($obj[0]->nombre, $obj[0]->descripcion, $obj[0]->estado, $obj[0]->precio, $obj[0]->imagen);
                $this->response(200,"success","Nuevo combo agregado");
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
        $stmt = $this->mysqliconn->prepare("DELETE FROM combo WHERE idCombo = ? ; ");
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
            if($_REQUEST['action']=='combos'){
                $db = new ComboDB();
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
    public function update($id, $nombreN, $descripcionN, $estadoN, $precioN, $imagenN) {
        if($this->checkID($id)){
            $stmt = $this->mysqliconn->prepare("UPDATE combo SET nombre=?, descripcion=?, estado=?, precio=?, imagen=? WHERE idCombo = ? ; ");
            $stmt->bind_param('sssdss', $nombreN, $descripcionN, $estadoN, $precioN, $imagenN ,$id);
            $r = $stmt->execute();
            $stmt->close();
            return $r;
        }
        return false;
    }

    /**
     * Actualiza un recurso
     */
    function updateCombo() {
        if( isset($_REQUEST['action']) && isset($_REQUEST['id']) ){
            if($_REQUEST['action']=='combos'){
                $obj = json_decode( file_get_contents('php://input') );
                $objArr = (array)$obj;
                if (empty($objArr)){
                    $this->response(422,"error","Nada que anadir. Comprobar json");
                } else if(isset($obj[0]->nombre) && isset($obj[0]->descripcion) && isset($obj[0]->estado) && isset($obj[0]->precio) && isset($obj[0]->imagen)){
                    $db = new ComboDB();
                    $db->update($_REQUEST['id'], $obj[0]->nombre, $obj[0]->descripcion, $obj[0]->estado, $obj[0]->precio, $obj[0]->imagen);
                    $this->response(200,"success","Combo actualizadd");
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
        $stmt = $this->mysqliconn->prepare("SELECT * FROM combo WHERE idCombo=?");
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