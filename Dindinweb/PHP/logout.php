<?php
// Inicia a sessão para poder acessá-la.
session_start();

// Remove todas as variáveis da sessão.
$_SESSION = array();

// Destrói a sessão.
session_destroy();

// Redireciona para a página inicial pública.
// Usamos ../index.php para voltar um nível de diretório e acessar o index na raiz.
header('Location: ../PHP/index.php');
exit();
