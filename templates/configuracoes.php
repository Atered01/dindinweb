<?php
require_once('../PHP/config.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

// Lógica para capturar mensagens da sessão
$mensagem_sucesso = null;
if (isset($_SESSION['sucesso_senha'])) {
    $mensagem_sucesso = $_SESSION['sucesso_senha'];
    unset($_SESSION['sucesso_senha']);
}

$mensagem_erro = null;
if (isset($_SESSION['erro_senha'])) {
    $mensagem_erro = $_SESSION['erro_senha'];
    unset($_SESSION['erro_senha']);
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/cadastro.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
</head>
<body>
    
    <?php include '../includes/header_logado.php'; ?>

    <main class="container">
        <div class="container" id="cadastro-container" style="margin-top: 2rem; width: 600px;">
            <h1>Configurações da Conta</h1>
            <p class="subtitle">Gerencie suas informações pessoais e de segurança.</p>
            
            <hr>

            <h3>Alterar Senha</h3>
            
            <?php if ($mensagem_sucesso): ?>
                <div class="success-message" style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #66bb6a; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                    <?php echo htmlspecialchars($mensagem_sucesso); ?>
                </div>
            <?php endif; ?>
            <?php if ($mensagem_erro): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($mensagem_erro); ?>
                </div>
            <?php endif; ?>

            <form action="../PHP/alterar_senha.php" method="POST">
                <div class="form-group">
                    <label for="senha_atual">Senha Atual</label>
                    <input type="password" id="senha_atual" name="senha_atual" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nova_senha">Nova Senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirmar_nova_senha">Confirme a Nova Senha</label>
                        <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" required minlength="6">
                    </div>
                </div>
                 <div class="button-group">
                    <button type="submit" class="register-button">Alterar Senha</button>
                </div>
            </form>

            <hr style="margin-top: 2rem;">

            <h3>Alterar Foto de Perfil</h3>
            <form action="../PHP/upload_foto.php" method="post" enctype="multipart/form-data" style="margin-top: 1rem;">
                <p>Selecione uma nova imagem para seu perfil (limite 2MB):</p>
                <input type="file" name="foto_perfil" id="foto" required accept="image/png, image/jpeg, image/gif">
                <div class="button-group">
                     <button type="submit" class="register-button">Salvar Nova Foto</button>
                </div>
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>