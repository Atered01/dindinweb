<?php
// Em Dindinweb/templates/empresa_login.php
require_once('../PHP/config.php');

$erro_login = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['email']) || empty($_POST['senha'])) {
        $erro_login = "E-mail e senha são obrigatórios.";
    } else {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        try {
            $sql = "SELECT id, nome_empresa, senha_hash FROM empresas WHERE email_contato = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $empresa = $stmt->fetch();

            if ($empresa && password_verify($senha, $empresa['senha_hash'])) {
                // Login bem-sucedido, cria a sessão da empresa
                session_regenerate_id(true);
                $_SESSION['empresa_id'] = $empresa['id'];
                $_SESSION['empresa_nome'] = $empresa['nome_empresa'];

                // Redireciona para o painel B2B
                header('Location: ' . BASE_URL . '/templates/painel_b2b.php');
                exit();
            } else {
                $erro_login = "E-mail ou senha de empresa inválidos.";
            }
        } catch (PDOException $e) {
            $erro_login = "Erro no sistema. Tente novamente.";
            error_log("Erro de login de empresa (PDO): " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login da Empresa - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/login.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
</head>

<body>
    <?php include '../includes/header_publico.php'; ?>

    <main>
        <div class="container" id="login-container">
            <div class="login-header">
                <div class="icon-circle">
                    <i class="fas fa-building"></i>
                </div>
                <h2>Portal da Empresa</h2>
            </div>

            <?php if ($erro_login): ?>
                <div class="error-message"><?php echo htmlspecialchars($erro_login); ?></div>
            <?php endif; ?>

            <form method="POST" action="empresa_login.php">
                <div class="form-group">
                    <label for="email">E-mail Corporativo</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                <button type="submit">Entrar</button>
            </form>
            <div class="info-link">
                <p>Não tem uma conta? <a href="<?php echo BASE_URL; ?>/templates/cadastro_empresa.php">Registre-se aqui</a></p>
            </div>
        </div>

    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>

</html>