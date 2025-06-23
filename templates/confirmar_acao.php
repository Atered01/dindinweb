<?php
// Em Dindinweb/templates/confirmar_acao.php
require_once('../PHP/config.php');

// Guarda de segurança do admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$acao = $_GET['acao'] ?? null;
$id_usuario_alvo = $_GET['id'] ?? null;
$id_admin_logado = $_SESSION['usuario_id'];
$erro = null;

// Validações iniciais
if (!$acao || !$id_usuario_alvo) {
    die("Ação ou ID do usuário não especificado.");
}

if ($id_usuario_alvo === $id_admin_logado) {
    die("Ação não permitida sobre a sua própria conta.");
}

// Lógica para processar o formulário de confirmação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_admin = $_POST['senha_admin'] ?? null;

    if (!$senha_admin) {
        $erro = "Por favor, insira sua senha para confirmar.";
    } else {
        // Verificar a senha do admin logado
        $stmt_admin = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE id_personalizado = ?");
        $stmt_admin->execute([$id_admin_logado]);
        $hash_admin = $stmt_admin->fetchColumn();

        if ($hash_admin && password_verify($senha_admin, $hash_admin)) {
            // Senha correta, executar a ação
            try {
                switch ($acao) {
                    case 'excluir':
                        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_personalizado = ?");
                        $stmt->execute([$id_usuario_alvo]);
                        break;

                    case 'tornar_admin':
                        // Lógica para alternar o status de admin
                        $stmt_status = $pdo->prepare("SELECT is_admin FROM usuarios WHERE id_personalizado = ?");
                        $stmt_status->execute([$id_usuario_alvo]);
                        $novo_status = !$stmt_status->fetchColumn();
                        
                        $stmt = $pdo->prepare("UPDATE usuarios SET is_admin = ? WHERE id_personalizado = ?");
                        $stmt->execute([$novo_status, $id_usuario_alvo]);
                        break;
                }
                // Redireciona para a home do admin após sucesso
                header('Location: admin_home.php');
                exit();

            } catch (PDOException $e) {
                die("Erro ao executar a ação: " . $e->getMessage());
            }
        } else {
            $erro = "Senha de administrador incorreta.";
        }
    }
}

// Determina a mensagem a ser exibida na página
$mensagem_confirmacao = '';
switch ($acao) {
    case 'excluir':
        $mensagem_confirmacao = "Você está prestes a <strong>EXCLUIR PERMANENTEMENTE</strong> o usuário com ID: " . htmlspecialchars($id_usuario_alvo) . ". Esta ação não pode ser desfeita.";
        break;
    case 'tornar_admin':
        $mensagem_confirmacao = "Você está prestes a <strong>ALTERAR O STATUS DE ADMINISTRADOR</strong> do usuário com ID: " . htmlspecialchars($id_usuario_alvo) . ".";
        break;
    default:
        die("Ação desconhecida.");
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Ação Administrativa</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/login.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <main>
        <div class="container" id="login-container" style="width: 500px;">
            <div class="login-header">
                <div class="icon-circle" style="background-color: #fef2f2; color: #ef4444;"><i class="fas fa-exclamation-triangle"></i></div>
                <h2>Confirmação Necessária</h2>
            </div>

            <p class="info-link" style="text-align: left; margin-bottom: 1.5rem;"><?php echo $mensagem_confirmacao; ?></p>
            
            <?php if ($erro): ?>
                <div class="error-message"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <form method="POST" action="confirmar_acao.php?acao=<?php echo htmlspecialchars($acao); ?>&id=<?php echo htmlspecialchars($id_usuario_alvo); ?>">
                <div>
                    <label for="senha_admin">Para continuar, digite sua senha de administrador:</label>
                    <input type="password" id="senha_admin" name="senha_admin" required autofocus>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                    <a href="admin_home.php" class="btn btn-secondary" style="text-decoration: none;">Cancelar</a>
                    <button type="submit" class="btn btn-primary" style="background-color: var(--color-red);">Confirmar Ação</button>
                </div>
            </form>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>