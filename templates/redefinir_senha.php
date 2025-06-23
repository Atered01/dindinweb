<?php
require_once('../PHP/config.php');

// PONTO CRÍTICO: Valida se o usuário passou pela etapa de verificação do código
if (!isset($_SESSION['email_para_redefinir'])) {
    // Se não passou, redireciona para o início do fluxo com uma mensagem de erro.
    $_SESSION['mensagem_recuperacao'] = "Processo inválido. Por favor, solicite a recuperação novamente.";
    header('Location: esqueci_senha.php');
    exit();
}

$email = $_SESSION['email_para_redefinir'];
$erro = '';
$sucesso = '';

// Processa o formulário de nova senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_nova_senha = $_POST['confirmar_nova_senha'] ?? '';

    if (strlen($nova_senha) < 6) {
        $erro = "A nova senha deve ter pelo menos 6 caracteres.";
    } elseif ($nova_senha !== $confirmar_nova_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        try {
            // Atualiza a senha do usuário
            $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt_update = $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE email = ?");
            $stmt_update->execute([$novo_hash, $email]);

            // Limpa a sessão para finalizar o processo
            unset($_SESSION['email_para_redefinir']);

            $_SESSION['mensagem_sucesso'] = "Sua senha foi redefinida com sucesso! Você já pode fazer o login.";
            header('Location: login.php');
            exit();
        } catch (Exception $e) {
            $erro = "Ocorreu um erro ao atualizar sua senha. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha - DinDin Verde</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/login.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
</head>

<body>
    <main>
        <div class="container" id="login-container">
            <h2>Crie uma Nova Senha</h2>
            <p class="info-link" style="text-align: left; margin-bottom: 1rem;">Você está redefinindo a senha para: <strong><?php echo htmlspecialchars($email); ?></strong></p>

            <?php if ($erro): ?>
                <div class="error-message"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <form method="POST" action="redefinir_senha.php">
                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" name="nova_senha" id="nova_senha" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirmar_nova_senha">Confirme a Nova Senha</label>
                    <input type="password" name="confirmar_nova_senha" id="confirmar_nova_senha" required>
                </div>
                <button type="submit">Redefinir Senha</button>
            </form>
        </div>
    </main>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>

</html>