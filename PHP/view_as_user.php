<?php
// Em Dindinweb/templates/view_as_user.php
require_once('../PHP/config.php');

// Apenas admins podem usar esta funcionalidade
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    // Cria a "flag" na sessão
    $_SESSION['view_as_user'] = true;
}

// Redireciona para a home de usuário comum
header('Location: ' . BASE_URL . '/templates/homeComLogin.php');
exit();
