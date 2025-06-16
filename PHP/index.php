<?php

require_once('../PHP/config.php');

// Verifica se o usuário está logado e se é um administrador
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    // Se for admin, redireciona para a home de administração
    header('Location: ' . BASE_URL . '/templates/admin_home.php');
    exit();
} 
// Verifica se é um usuário comum logado
elseif (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    // Se for, redireciona para a home de usuário logado
    header('Location: ' . BASE_URL . '/templates/homeComLogin.php');
    exit();
} 
// Se não for nenhum dos dois, é um visitante
else {
    // Redireciona para a home pública
    header('Location: ' . BASE_URL . '/templates/homeSemLogin.php');
    exit();
}
?>