<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = new \slim\App;

//GET Todos
$app->get('/api/personas', function(request $request, Response $response){
    $sql = "SELECT * FROM personas";
    $prueba = $request->getHeader("X-Foo");
    print_r($prueba);
    die();
    try{
        $db = new db();
        $db = $db->conecctionDb();
        $resultado = $db->query($sql);
        if($resultado->rowCount() > 0){
            $personas = $resultado->fetchAll(PDO::FETCH_OBJ);
            
            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json')
                // ->withHeader('Access-Control-Allow-Headers', '*')
                ->write(json_encode($personas));
        } else{
            echo json_encode(array('status'=>array('message'=> 'Sin registros', 'type'=>'error')));
        }
        $resultado = null;
        $db = null;
    } catch(PDOException $e){
        echo '{"error":{"text":'.$e.getMessage().'}}';
    }
});

//GET por id
$app->get('/api/personas/{id}', function(request $request, Response $response){
    $id_persona = $request->getAttribute('id');
    $sql = "SELECT * FROM personas WHERE Id = $id_persona";
    try{
        $db = new db();
        $db = $db->conecctionDb();
        $resultado = $db->query($sql);
        if($resultado->rowCount() > 0){
            $personas = $resultado->fetchAll(PDO::FETCH_OBJ);
            echo json_encode($personas);
        } else{
            echo json_encode(array('status'=>array('message'=> 'La persona no existe', 'type'=>'error')));
        }
        $resultado = null;
        $db = null;
    } catch(PDOException $e){
        echo '{"error":{"text":'.$e.getMessage().'}}';
    }
});

//POST
$app->post('/api/personas/add', function(request $request, Response $response){
    $nombre = $request->getParam('nombre');
    $apellido = $request->getParam('apellido');
    $sql = "INSERT INTO personas(Nombre,Apellido) VALUES(:nombre, :apellido)";
    try{
        $db = new db();
        $db = $db->conecctionDb();
        $resultado = $db->prepare($sql);

        $resultado->bindParam(':nombre', $nombre);
        $resultado->bindParam(':apellido', $apellido);
        // $resultado->execute();

        try {
            // $db->beginTransaction();
            $resultado->execute();
            // $db->commit();
            echo json_encode(array('status'=>array('message'=> $db->lastInsertId().' La persona se ha guardado', 'type'=>'ok')));
        } catch(PDOExecption $e) {
            $db->rollback();
            echo json_encode(array('status'=>array('message'=> $e->getMessage().' El usuario no existe', 'type'=>'error')));
        }

        // if($resultado->rowCount() > 0){
        //     $personas = $resultado->fetchAll(PDO::FETCH_OBJ);
        //     echo json_encode($personas);
        // } else{
        //     echo json_encode(array('status'=>array('message'=> 'El usuario no existe', 'type'=>'error')));
        // }
        $resultado = null;
        $db = null;
    } catch(PDOException $e){
        echo '{"error":{"text":'.$e.getMessage().'}}';
    }
});

//PUT por id
$app->put('/api/personas/edit/{id}', function(request $request, Response $response){
    $id_persona = $request->getAttribute('id');
    $nombre = $request->getParam('nombre');
    $apellido = $request->getParam('apellido');
    $sql = "UPDATE personas SET Nombre = :nombre, Apellido = :apellido WHERE Id = :id";
    try{
        $db = new db();
        $db = $db->conecctionDb();
        $resultado = $db->prepare($sql);
        $resultado->bindParam(':nombre', $nombre);
        $resultado->bindParam(':apellido', $apellido);
        $resultado->bindParam(':id', $id_persona);
        try {
            $resultado->execute();
            echo json_encode(array('status'=>array('message'=> $resultado->rowCount().' La persona se ha actualizado', 'type'=>'ok')));
        } catch(PDOExecption $e) {
            $db->rollback();
            echo json_encode(array('status'=>array('message'=> $e->getMessage().' El usuario no existe', 'type'=>'error')));
        }
        $resultado = null;
        $db = null;
    } catch(PDOException $e){
        echo '{"error":{"text":'.$e.getMessage().'}}';
    }
});

//DELETE por id
$app->delete('/api/personas/delete/{id}', function(request $request, Response $response){
    $id_persona = $request->getAttribute('id');
    $sql = "DELETE FROM personas WHERE Id = :id";
    try{
        $db = new db();
        $db = $db->conecctionDb();
        $resultado = $db->prepare($sql);
        $resultado->bindParam(':id', $id_persona);
        try {
            $resultado->execute();
            echo json_encode(array('status'=>array('message'=> $resultado->rowCount().' La persona se ha eliminado', 'type'=>'ok')));
        } catch(PDOExecption $e) {
            $db->rollback();
            echo json_encode(array('status'=>array('message'=> $e->getMessage().' El usuario no existe', 'type'=>'error')));
        }
        $resultado = null;
        $db = null;
    } catch(PDOException $e){
        echo '{"error":{"text":'.$e.getMessage().'}}';
    }
});