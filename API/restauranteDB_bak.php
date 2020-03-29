<?php
require_once("db/conexion.php");
class RestauranteDB {
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
    public function getTablaOne($tabla = "", $campos = "", $where = ""){
        $resultado = array();
        $error = array();
        if($tabla != "" && is_array($campos) && (is_array($where) && count($where) > 1)){
            $select_campo = implode(', ', $campos);
            $stmt = $this->mysqliconn->prepare("SELECT ".$select_campo." FROM ".$tabla." WHERE ".$where['nombre']."=? ; ");
            $stmt->bind_param('s', $where['id']);
            $stmt->execute();

            $meta = $stmt->result_metadata();
            while ($field = $meta->fetch_field()){ 
                $params[] = &$row[$field->name]; 
            }
            call_user_func_array(array($stmt, 'bind_result'), $params); 

            while ($stmt->fetch()) { 
                foreach($row as $key => $val) 
                { 
                    $c[$key] = $val; 
                } 
                $resultado[] = $c;
            }
            $stmt->close();
        } else{
            if($tabla == ""){
                $error[] = "La tabla no esta especificada";
            }
            if(!is_array($campos)){
                $error[] = "Las columnas no se han especificado";
            }
            if( (!is_array($where))  && (count($where) < 2) ){
                if(!is_array($where)){
                    $error[] = "El filtro no se ha especificado";
                } else if(count($where) < 2){
                    $error[] = "El filtro no cuenta con los parametros necesarios";
                }
            }

            if(count($error) > 0){
                $resultado[] = array('error'=> implode(', ', $error));
            }
        }
        
        return $resultado;
    }

    /**
     * obtiene todos los registros de la tabla
     * @return Array, array con los registros obtenidos de la base de datos
     */
    public function getTablaAll($tabla){
        $result = $this->mysqliconn->query('SELECT * FROM '.$tabla.' where estado = 1');
        $resultado = array();
        while ($row = $result->fetch_assoc()) {
            $resultado[] = $row;
        }
        $result->close();
        return $resultado;
    }

    function getCombos(){
        if($_REQUEST['action'] == 'combos'){
            $db = new RestauranteDB();
            if(isset($_REQUEST['id'])){
                $campos = array('idCombo', 'nombre', 'descripcion', 'precio', 'imagen');
                $where = array('nombre'=>'idCombo', 'id'=>$_REQUEST['id']);
                $response = $db->getTablaOne('combo', $campos, $where);
                echo json_encode($response,JSON_FORCE_OBJECT);
            } else{
                $response = $db->getTablaAll('combo');
                $respuesta_json = json_encode($response) ;
                echo $respuesta_json;
            }
        } else{
            $this->response(400);
        }
    }

    function getComandas(){
        if($_REQUEST['action'] == 'comandas'){
            $db = new restauranteDB();
            if(isset($_REQUEST['id'])){
                $campos = array('idComanda', 'idCombo', 'descripcion', 'total', 'fechaRegistro', 'fechaDespacho');
                $where = array('nombre'=>'idComanda', 'id'=>$_REQUEST['id']);
                $response = $db->getTablaOne('comanda', $campos, $where);
                echo json_encode($response, JSON_FORCE_OBJECT);
            } else{
                $response = $db->getTablaAll('comanda');
                $respuesta_json = json_encode($response) ;
                echo $respuesta_json;
            }
        } else{
            $this->response(400);
        }
    }

    function getClaveUsuario(){
        if($_REQUEST['action'] == 'usuarios'){
            $obj = json_decode(file_get_contents('php://input'));
            $objArr = (array)$obj;

            if (empty($objArr)){
                $this->response(422,"error","Nada que anadir. Comprobar json");
            }else if(isset($obj->usuario) && isset($obj->password) ){
                $estado_us = 0; //variable para saber si se encontro el usuario
                $db = new restauranteDB();
                $response = $db->getTablaAll('usuario'); //traemos todos los datos del usuario
                foreach ($response as $key => $value) {
                    //hash('sha512', $obj->password)
                    if($value['usuario'] == $obj->usuario && $value['clave'] == $obj->password){
                        $estado_us = 1;
                    }
                }
                if($estado_us != 0){
                    $this->response(200,"success","Nuevo combo agregado");
                } else{
                    $this->response(422,"error","Clave o contraseña incorrecto");
                }
            }else{
                $this->response(422,"error","La propiedad no esta definida");
            }
        } else{
            $this->response(400);
        }
    }

    function getAutor(){
        if($_REQUEST['action'] == 'autor'){
            $response = array('nombre'=>'Edith Elena Herrera Ramirez', 'carnet'=>'hr103216', 'carrera'=>'Ingenieria en sistemas informaticos', 'foto'=>'http://rest.smycode.com/img/edith.jpg');
            echo json_encode($response);
        } else{
            $this->response(400);
        }
    }

    function setOrden(){
        if($_REQUEST['action'] == 'agregarorden'){
            $obj = json_decode(file_get_contents('php://input'));
            $objArr = (array)$obj; //arreglo que viene de la aplicacion

            if (empty($objArr)){
                $this->response(422,"error","Comprobar json");
            } else if(isset($obj->idCombo) && isset($obj->descripcion) && isset($obj->total) && isset($obj->estado) ){
                $db = new restauranteDB();
                $datos_guardar = array('idCombo'=>$obj->idCombo, 'descripcion'=>$obj->descripcion,'total'=>$obj->total,'estado'=>$obj->estado); //arreglo para guardar
                $estado = $db->insert('comanda',$datos_guardar);
                if($estado != 0){
                    $this->response(200,"success","Nueva comanda agregada");
                } else{
                    $this->response(422,"error","No se pudo guardar el registro");
                }
            }else{
                $this->response(422,"error","La propiedad no esta definida");
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
    public function insert($tabla, $datos){
        $columnas_array = array();
        $incognita_array = array();
        $value_array = [];
        $tipos = '';
        $db = new restauranteDB();
        foreach ($datos as $key => $value) {
            if(trim($value) != ''){
                $columnas_array[] = $key;
                $incognita_array[] = '?';
                $tipos_temp = $db->tipo_dato($value);
                if($tipos_temp=='s'){
                    $value_array[] = (string)$value;
                } else if($tipos_temp == 'd'){
                    $value_array[] = (double)$value;
                } else if($tipos_temp == 'i'){
                    $value_array[] = (integer)$value;
                }
                $tipos .= $tipos_temp;
            }
        }
        $columnas = implode(', ', $columnas_array);
        $incognita = implode(', ', $incognita_array);
        // print_r("INSERT INTO $tabla($columnas) VALUES ($incognita); ");
        // print_r($value_array);
        // die();
        //queria hacerlo dinamico pero no me dejo xD
        
        $stmt = $this->mysqliconn->prepare("INSERT INTO $tabla($columnas) VALUES ($incognita); ");
        $stmt->bind_param('isdi', $value_array[0], $value_array[1], $value_array[2], $value_array[3]);
        $r = $stmt->execute();
        $stmt->close();
        print_r($r);
        return $r;
    }

    public function tipo_dato($variable){
        $punto_decimal = explode(".", trim($variable));
        $regresar = '';
        if(count($punto_decimal) > 2){
            $regresar = 's';
        } else{
            if(count($punto_decimal) == 2){
                if(is_float( ( (is_numeric($variable))?(float)$variable:'asd') ) ){
                    $regresar = 'd';
                } else{
                    $regresar = 's';
                }
            } else if(is_numeric($variable)){
                $regresar = 'i';
            } else{
                $regresar = 's';
            }
        }
        return $regresar;
    }

    function setDespachada(){
        if($_REQUEST['action'] == 'despachar'){
            $obj = json_decode(file_get_contents('php://input'));
            $objArr = (array)$obj; //arreglo que viene de la aplicacion

            if (empty($objArr)){
                $this->response(422,"error","Comprobar json");
            } else if(isset($obj->idComanda) && isset($obj->estado) ){
                $db = new restauranteDB();
                $fecha = date('Y-m-d H:i:s');
                $estado = $db->update('comanda',$obj->idComanda, $obj->estado, $fecha);
                if($estado != 0){
                    $this->response(200,"success","La comanda se despacho");
                } else{
                    $this->response(422,"error","No se pudo actualizar la comanda");
                }
            }else{
                $this->response(422,"error","La propiedad no esta definida");
            }
        } else{
            $this->response(400);
        }
    }

    /**
     * Actualiza registro dado su ID
     * @param int $id Description
     */
    public function update($tabla, $idComanda, $estado, $fecha) {

        if($this->checkID($tabla, $idComanda)){
            $stmt = $this->mysqliconn->prepare("UPDATE $tabla SET estado=?, fechaDespacho=? WHERE idComanda = ?; ");
            $stmt->bind_param('ssi', $estado, $fecha, $idComanda);
            $r = $stmt->execute();
            $stmt->close();
            return $r;
        }
        return false;
    }

    /**
    * verifica si un ID existe
    * @param int $id Identificador unico de registro
    * @return Bool TRUE|FALSE
    */
    public function checkID($tabla, $id){
        $stmt = $this->mysqliconn->prepare("SELECT * FROM $tabla WHERE idComanda=?");
        $stmt->bind_param("i", $id);
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