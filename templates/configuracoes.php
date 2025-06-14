<?php
session_start();
require_once('../PHP/config.php');

// O "guarda" de segurança para garantir que o usuário está logado
if (!isset($_SESSION['usuario_id'])) { 
    header('Location: ' . BASE_URL . '/templates/login.php'); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/perfil.css">
</head>
<body>
    
    <?php include '../includes/header_logado.php'; ?>

    <div class="perfil-container">
        <main class="conteudo-principal" style="flex: 1;">
            <h1>Configurações da Conta</h1>
            <p>Em breve, aqui você poderá alterar seus dados pessoais, senha e preferências de notificação.</p>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>