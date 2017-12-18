<?php
require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$db = new mysqli('localhost', 'root', '', 'curso_angular');

$app->get('/pruebas', function() use($app, $db){
  echo 'Hola mundo desde Slim';
  var_dump($db);
});

$app->get('/saludo', function() use($app){
  echo 'Buenas noches';
});

$app->run();
