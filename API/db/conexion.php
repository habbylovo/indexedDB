<?php
require_once("config.php");
class baseDatos{
    private $conexion;
    private $db;

    public static function conectar(){
        $conexion = mysqli_connect(host, user, pass, dbname, port);

        //Set de caracteres utf8
        mysqli_set_charset($conexion, "utf8");

        if($conexion->connect_errno){
            die("Lo sentimos, no se ha podido establecer la conexion con Mysql: ".mysqli_error());
        } else{
            $db = mysqli_select_db($conexion, dbname);
            if($db == 0){
                die("Lo sentimos, no se ha podido conectar con la base de datos: ".dbname);
            }
        }

        return $conexion;
    }

    public function desconectar($conexion){
        if($conexion){
            mysqli_close($conexion);
        }
    }
}
?>