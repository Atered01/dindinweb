<?php
session_start();
if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    header('Location: ../templates/homeComLogin.php');
    exit();
} else {
    // Altere aqui de .html para .php
    header('Location: ../templates/homeSemLogin.php');
    exit();
}
?>