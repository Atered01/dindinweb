<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$projectName = 'Dindinweb';
define('BASE_URL', $protocol . $host . '/' . $projectName);
define('PROJECT_ROOT', dirname(__DIR__));
// Conexão PDO
$db_name = 'embalagens_db';
$db_host = "localhost";
$db_user = "root";
$db_pass = '';
try {
    $pdo = new PDO("mysql:dbname=$db_name;host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Erro na conexão: ". $e->getMessage());
    die("Erro no sistema. Por favor, tente novamente mais tarde.");
}
?>