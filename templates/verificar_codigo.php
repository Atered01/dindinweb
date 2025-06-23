<?php
require_once('../PHP/config.php');

$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_post = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $codigo = preg_replace('/[^0-9]/', '', $_POST['codigo'] ?? '');

    if (!$email_post || empty($codigo)) {
        $erro = "E-mail ou código inválido.";
    } else {
        $token_hash = hash('sha256', $codigo);
        $agora = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("SELECT * FROM recuperacao_senha WHERE usuario_email = ? AND token_hash = ? AND data_expiracao > ?");
        $stmt->execute([$email_post, $token_hash, $agora]);
        $solicitacao = $stmt->fetch();

        if ($solicitacao) {
            $_SESSION['email_para_redefinir'] = $email_post;

            $stmt_delete = $pdo->prepare("DELETE FROM recuperacao_senha WHERE usuario_email = ?");
            $stmt_delete->execute([$email_post]);

            header('Location: redefinir_senha.php');
            exit();
        } else {
            $erro = "Código inválido ou expirado. Tente novamente.";
        }
    }
    $email = $email_post;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/login.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
</head>

<body>
    <?php include '../includes/header_publico.php'; ?>

    <main>
        <div class="container" id="login-container" style="margin-top: 3rem; margin-bottom: 3rem;">
            <div class="login-header">
                <div class="icon-circle verify">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2>Verificar Código</h2>
            </div>
            <p class="info-link" style="text-align: center; margin-bottom: 1rem;">Digite o código de 7 dígitos que enviamos para seu e-mail.</p>

            <?php if ($erro): ?>
                <div class="error-message"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            <form method="POST" action="verificar_codigo.php">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <div class="form-group">
                    <label for="codigo">Código de Verificação</label>
                    <input type="text" name="codigo" id="codigo" required maxlength="7" pattern="\d{7}" title="Digite o código de 7 dígitos.">
                </div>
                <button type="submit">Verificar e Continuar</button>
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>

</html>