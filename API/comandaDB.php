<?php
require_once("db/conexion.php");
class ComandaDB {
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
    public function getComanda($id=0){
        $stmt = $this->mysqliconn->prepare("SELECT idComanda, idCombo, descripcion, total, fechaRegistro, fechaDespacho FROM comanda WHERE idComanda=? ; ");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->bind_result($col1, $col2, $col3, $col4, $col5, $col6);
        $comandas = array();
        while ($result->fetch()) {
            $comandas[] = ['idComanda'=>$col1, 'idCombo'=>$col2, 'descripcion'=>$col3, 'total'=>$col4, 'fechaRegistro'=>$col5, 'fechaDespacho'=>$col6];
        }
        $stmt->close();
        return $comandas;
    }

    /**
     * obtiene todos los registros de la tabla "personas"
     * @return Array array con los registros obtenidos de la base de datos
     */
    public function getComandas2(){
        $result = $this->mysqliconn->query('SELECT * FROM comanda');
        $comandas = array();
        while ($row = $result->fetch_assoc()) {
            $comandas[] = $row;
        }
        $result->close();
        return $comandas;
    }

    function getComandas(){
        if($_REQUEST['action'] == 'comandas'){
            $db = new ComandaDB();
            if(isset($_REQUEST['id'])){
                $response = $db->getComanda($_REQUEST['id']);
                echo json_encode($response, JSON_FORCE_OBJECT);
            } else{
                $response = $db->getComandas2();
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
    public function insert($nombre, $descripcion, $total, $estado, $precio, $imagen){
        $stmt = $this->mysqliconn->prepare("INSERT INTO comanda(nombre, descripcion, total, estado, fechaRegistro, fechaDespacho) VALUES (?, ?, ?, ?, ?, ?); ");
        $stmt->bind_param('ssdsss', $nombre, $descripcion, $total, $estado, $precio, $imagen);
        $r = $stmt->execute();
        $stmt->close();
        return $r;
    }

    /**
     * metodo para guardar un nuevo registro de persona en la base de datos
     */
    function savePeople(){
        if($_REQUEST['action']=='comandas'){
            $obj = json_decode(file_get_contents('php://input'));
            $objArr = (array)$obj;
            if (empty($objArr)){
                $this->response(422,"error","Nada que anadir. Comprobar json");
            }else if(isset($obj[0]->nombre) && isset($obj[0]->descripcion) && isset($obj[0]->total) && isset($obj[0]->estado) && isset($obj[0]->precio) && isset($obj[0]->imagen)){
                $combo = new ComandaDB();
                $combo->insert($obj[0]->nombre, $obj[0]->descripcion, $obj[0]->total, $obj[0]->estado, $obj[0]->precio, $obj[0]->imagen);
                $this->response(200,"success","Nueva comanda agregada");
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
        $stmt = $this->mysqliconn->prepare("DELETE FROM comanda WHERE idComanda = ? ; ");
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
            if($_REQUEST['action']=='comandas'){
                $db = new ComandaDB();
                $db->delete($_REQUEST['id']);
                $this->response(204);
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
        $stmt = $this->mysqliconn->prepare("SELECT * FROM comanda WHERE idComanda=?");
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