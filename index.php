<?php
require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$db = new mysqli('localhost', 'root', '', 'curso_angular');

// Configuracion de cabeceras
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
	die();
}

$app->get('/pruebas', function() use($app, $db){
  echo 'Hola mundo desde Slim';
  var_dump($db);
});

$app->get('/saludo', function() use($app){
  echo 'Buenas noches';
});

// METODO POR POST PARA GUARDAR PRODUCTO
$app->post('/producto',function() use($app, $db){
  // Obtener los datos que llegan en la request por POST
  $json=$app->request->post('json');
  // Decodificar el valor de json para convertirlo en un array de objetos php
  $data=json_decode($json,true);
  // Comprobar lo que contienen
  // var_dump($json);
  // var_dump($data);
  // Verificar si existen los datos enviados
  if(!isset($data['nombre'])){
    $data['nombre']=null;
  }
  if(!isset($data['precio'])){
    $data['precio']=null;
  }
  if(!isset($data['descripcion'])){
    $data['descripcion']=null;
  }
  if(!isset($data['imagen'])){
    $data['imagen']=null;
  }
  // Realizar un insert a la base de datos
  $query="INSERT INTO productos VALUES(NULL,  ".
         "'{$data['nombre']}',".
         "'{$data['descripcion']}',".
         "{$data['precio']},".
         "'{$data['imagen']}'".
         ");";
  // Mostrar la consulta
  // var_dump($query);
  // Ejecutar la consulta
  $insert=$db->query($query);
  // Definir una respuesta al usuario por defecto
  $result=array(
    'status'=>'error',
    'code'=>500,
    'message'=>'Error al crear producto'
  );
  // Verificar si se ejecuto la consulta correctamente
  if($insert){
    $result=array(
      'status'=>'success',
      'code'=>200,
      'message'=>'Producto creado correctamente'
    );
  }
  // Devolver el resultado como json
  echo json_encode($result);
});

// LISTAR TODOS LOS PRODUCTOS
$app->get('/producto', function() use($app, $db){
  // Preparar la consulta sql
  $sql='SELECT * FROM productos ORDER BY id DESC';
  // Ejecutar la consulta
  $query=$db->query($sql);
  // Mostrar el resultado de la consulta
  // var_dump($query->fetch_all());
  // Generar un array de objetos, y que fetch_assoc devuelve un array asociativo.
  $productos=array();
  while($producto=$query->fetch_assoc()){
    $productos[]=$producto;
  }
  $result=array(
    'status'=>'success',
    'code'=>200,
    'data'=>$productos
  );
  // Devolver el resultado como json
  echo json_encode($result);
});

// RETORNAR UN SOLO PRODUCTO
$app->get('/producto/:id', function($id) use($app, $db){
  // Preparar la consulta sql
  $sql='SELECT * FROM productos WHERE id='.$id;
  // Ejecutar la consulta
  $query=$db->query($sql);
  // Definir una respuesta al usuario por defecto
  $result=array(
    'status'=>'error',
    'code'=>500,
    'message'=>'Producto no encontrado'
  );
  // Comprobar la consulta
  if($query->num_rows==1){
    // fetch_assoc devuelve un array asociativo
    $producto=$query->fetch_assoc();
    $result=array(
      'status'=>'success',
      'code'=>200,
      'data'=>$producto
    );
  }
  // Devolver el resultado como json
  echo json_encode($result);
});

// ELIMINAR UN PRODUCTO
$app->get('/producto/delete/:id', function($id) use($app, $db){
  // Preparar la consulta sql
  $sql='DELETE FROM productos WHERE id='.$id;
  // Ejecutar la consulta
  $query=$db->query($sql);
  // Definir una respuesta al usuario por defecto
  $result=array(
    'status'=>'error',
    'code'=>500,
    'message'=>'Producto no se ha eliminado.'
  );
  // Verificar que se ha ejecutado exitosamente
  if($query){
    $result=array(
      'status'=>'success',
      'code'=>200,
      'message'=>'El producto se ha eliminado correctamente.'
    );
  }
  // Devolver el resultado como json
  echo json_encode($result);
});

// ACTUALIZAR UN PRODUCTO
$app->post('/producto/update/:id',function($id) use($app, $db){
  // Obtener los datos que llegan en la request por POST
  $json=$app->request->post('json');
  // Decodificar el valor de json para convertirlo en un array de objetos php
  $data=json_decode($json,true);
  // Preparar la consulta sql
  $sql="UPDATE productos SET ".
        "nombre='{$data["nombre"]}', ".
        "descripcion='{$data["descripcion"]}', ".
        "precio={$data["precio"]}";
  // Verificar si tiene una imagen
  if(isset($data['imagen'])){
    $sql.=", imagen='{$data["imagen"]}'";
  }
  $sql.=" WHERE id={$id}";
  // Mostrar la consulta
  // var_dump($sql);
  // Ejecutar la consulta
  $query=$db->query($sql);
  // Verificar la consulta
  if($query){
    $result=array(
      'status'=>'success',
      'code'=>200,
      'message'=>'El producto se ha actualizado correctamente.'
    );
  }else{
    $result=array(
      'status'=>'error',
      'code'=>500,
      'message'=>'Producto no se ha actualizado.'
    );
  }
  // Devolver el resultado como json
  echo json_encode($result);
});

// SUBIR UNA IMAGEN A UN PRODUCTO
$app->post('/producto/upload', function() use($app, $db){
  // Respuesta por defecto
  $result=array(
    'status'=>'error',
    'code'=>500,
    'message'=>'El archivo no se ha subido.'
  );
  // Verificar si existe archivo con el nombre uploads
  if(isset($_FILES['uploads'])){
    // Para subir imagen instanciar un objeto $piramideUploader
    $piramideUploader=new PiramideUploader();
    // Subir el archivo con upload(prefijo_nombre, nombre_con_el_que_llega, directorio_donde_guardar, tipos_archivos)
    $upload=$piramideUploader->upload('image','uploads','uploads',array('image/jpeg','image/png','image/gif'));
    // Obtener la informacion del archivo que se ha subido
    $file=$piramideUploader->getInfoFile();
    $file_name=$file['complete_name'];
    // Comprobar el archivo ha subir
    // var_dump($file);
    // Verificar si se ha subido correctamente
    if (isset($upload) && $upload['uploaded']) {
      $result=array(
        'status'=>'success',
        'code'=>200,
        'message'=>'El archivo se ha subido.',
        'filename'=>$file_name
      );
    }
  }
  // Devolver el resultado como json
  echo json_encode($result);
});
$app->run();
