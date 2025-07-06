<?php
// Em Dindinweb/templates/cadastro_empresa.php
require_once('../PHP/config.php');

$erro = $_SESSION['erro_cadastro_empresa'] ?? null;
unset($_SESSION['erro_cadastro_empresa']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Empresa - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/login.css"> <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
</head>
<body>
    <?php include '../includes/header_publico.php'; ?>

    <main>
        <div class="container" id="login-container" style="width: 500px;">
            <div class="login-header">
                <div class="icon-circle">
                    <i class="fas fa-building"></i>
                </div>
                <h2>Cadastro de Empresa Parceira</h2>
            </div>
            <p class="info-link" style="text-align: center; margin-bottom: 1.5rem;">Junte-se à nossa rede de parceiros e faça parte da revolução sustentável.</p>

            <?php if ($erro): ?>
                <div class="error-message"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <form action="../PHP/registrar_empresa.php" method="POST">
                <div class="form-group">
                    <label for="nome_empresa">Nome da Empresa</label>
                    <input type="text" id="nome_empresa" name="nome_empresa" required>
                </div>
                <div class="form-group">
                    <label for="email_contato">E-mail de Contato</label>
                    <input type="email" id="email_contato" name="email_contato" required>
                </div>
                <div class="form-group">
                    <label for="senha">Crie uma Senha</label>
                    <input type="password" id="senha" name="senha" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirmar_senha">Confirme a Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                </div>
                <button type="submit">Cadastrar Empresa</button>
            </form>
            <div class="info-link">
                <p>Já é um parceiro? <a href="empresa_login.php">Acesse o portal aqui</a>.</p>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>