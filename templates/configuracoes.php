<?php
session_start();
require_once('../PHP/config.php');

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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/perfil.css"> </head>
<body>
    
    <?php include '../includes/header_logado.php'; ?>

    <main class="container">
        <div class="config-container">
            <h1>Configurações da Conta</h1>
            <p>Gerencie suas informações pessoais e de segurança.</p>
            
            <hr>

            <h3>Alterar Foto de Perfil</h3>
            <div class="upload-form-config">
                <form action="../PHP/upload_foto.php" method="post" enctype="multipart/form-data">
                    <p>Selecione uma nova imagem para seu perfil:</p>
                    <input type="file" name="foto_perfil" id="foto" required>
                    <button type="submit" class="btn" style="margin-top: 1rem;">Salvar Nova Foto</button>
                </form>
            </div>

            <hr>

            <h3>Alterar Senha</h3>
            <p>Em breve, aqui você poderá alterar sua senha.</p>

            <hr>

            <h3>Preferências de Notificação</h3>
            <p>Em breve, aqui você poderá gerenciar suas notificações.</p>

        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>