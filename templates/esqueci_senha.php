<?php
require_once('../PHP/config.php');
$mensagem = $_SESSION['mensagem_recuperacao'] ?? null;
unset($_SESSION['mensagem_recuperacao']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha - DinDin Verde</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/login.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
</head>

<body>
    <?php include '../includes/header_publico.php'; ?>
    <main>
        <div class="container" id="login-container">
            <h2>Recuperar Senha</h2>
            <p class="info-link" style="text-align: left; margin-bottom: 1rem;">Digite seu e-mail cadastrado. Se ele existir em nosso sistema, enviaremos um link para redefinir sua senha.</p>

            <?php if ($mensagem): ?>
                <div class="success-message"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>

            <form action="../PHP/solicitar_recuperacao.php" method="POST">
                <div>
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit">Enviar Link de Recuperação</button>
            </form>
        </div>
    </main>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
    <?php include '../includes/footer.php'; ?>
</body>

</html>