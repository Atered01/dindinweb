<?php
// Em Dindinweb/config.php

// Define a URL base do projeto. Mude isso se o nome da pasta do seu projeto mudar.
define('BASE_URL', '/Dindinweb');

// O resto do seu código de conexão PDO continua aqui...
$db_name = 'embalagens_db';
$db_host = "localhost";
$db_user = "root";
$db_pass = '';
try{
    $pdo = new PDO("mysql:dbname=" . $db_name . ";host=" . $db_host, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e){
    error_log("Erro na conexão: ". $e->getMessage());
    die("Erro no sistema. Por favor, tente novamente mais tarde.");
}
?>