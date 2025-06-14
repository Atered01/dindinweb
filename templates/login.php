<?php
// A sessão deve ser sempre a primeira coisa a ser iniciada
session_start();

// Inclui o config.php para definir a BASE_URL e a conexão $pdo
require_once('../PHP/config.php');

// Variáveis para mensagens
$mensagem_sucesso = null;
$erro_login = null;

// Verifica se existe uma mensagem de sucesso vinda do cadastro
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = $_SESSION['mensagem_sucesso'];
    unset($_SESSION['mensagem_sucesso']);
}

// Lógica de processamento de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (empty($_POST['email']) || empty($_POST['senha'])) {
        $erro_login = "E-mail e senha são obrigatórios.";
    } else {
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        
        try {
            // Usando a chave primária 'id' numérica para a sessão
            $sql = "SELECT id, nome, senha_hash FROM usuarios WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                
                header('Location: ' . BASE_URL . '/templates/homeComLogin.php');
                exit();
                
            } else {
                $erro_login = "E-mail ou senha inválidos.";
            }
        } catch (PDOException $e) {
            $erro_login = "Erro no sistema. Tente novamente.";
            error_log("Erro de login (PDO): " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/login.css">
</head>
<body>

    <?php 
        if (file_exists('../includes/header_publico.php')) {
            include '../includes/header_publico.php';
        }
    ?>

    <main>
        <div class="container" id="login-container">
            <div class="login-header">
                <div class="icon-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <h2>Login</h2>
            </div>

            <?php if (isset($mensagem_sucesso)): ?>
                <div class="success-message"><?php echo htmlspecialchars($mensagem_sucesso); ?></div>
            <?php endif; ?>

            <?php if (isset($erro_login)): ?>
                <div class="error-message"><?php echo htmlspecialchars($erro_login); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/templates/login.php">
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                <button type="submit">Entrar</button>
            </form>

            <div class="info-link">
                <p>Não tem uma conta? <a href="<?php echo BASE_URL; ?>/templates/cadastro.php">Registre-se aqui</a></p>
            </div>
        </div>
    </main>

    <?php 
        if (file_exists('../includes/footer.php')) {
            include '../includes/footer.php';
        }
    ?>

    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>