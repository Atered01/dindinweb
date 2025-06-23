<?php
// Em Dindinweb/templates/editar_usuario.php
require_once('../PHP/config.php');

// Guarda de segurança do admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/PHP/index.php');
    exit();
}

$id_usuario = $_GET['id'] ?? null;
if (!$id_usuario) {
    die("ID do usuário não fornecido.");
}

$erros = [];

// Lógica para salvar as alterações quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_nova_senha = $_POST['confirmar_nova_senha'] ?? '';
    $senha_admin = $_POST['admin_senha'] ?? null;

    // 1. Validar senha do admin (sempre obrigatória)
    if (empty($senha_admin)) {
        $erros[] = "A sua senha de administrador é obrigatória para salvar as alterações.";
    } else {
        $id_admin_logado = $_SESSION['usuario_id'];
        $stmt_admin = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE id_personalizado = ?");
        $stmt_admin->execute([$id_admin_logado]);
        $hash_admin = $stmt_admin->fetchColumn();

        if (!$hash_admin || !password_verify($senha_admin, $hash_admin)) {
            $erros[] = "Senha de administrador incorreta.";
        }
    }

    // 2. Bloco para lidar com a alteração de senha (se preenchida)
    $atualizar_senha = false;
    if (!empty($nova_senha)) {
        if (strlen($nova_senha) < 6) {
            $erros[] = "A nova senha deve ter pelo menos 6 caracteres.";
        }
        if ($nova_senha !== $confirmar_nova_senha) {
            $erros[] = "As novas senhas não coincidem.";
        }
        if (empty($erros)) {
            $atualizar_senha = true;
        }
    }

    // 3. Se não houver erros de validação, executa o UPDATE
    if (empty($erros)) {
        try {
            // Monta a query e os parâmetros dinamicamente
            $params = [$nome, $email];
            if ($atualizar_senha) {
                $sql = "UPDATE usuarios SET nome = ?, email = ?, senha_hash = ? WHERE id_personalizado = ?";
                $params[] = password_hash($nova_senha, PASSWORD_DEFAULT);
            } else {
                $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id_personalizado = ?";
            }
            $params[] = $id_usuario;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            header('Location: admin_home.php');
            exit();
        } catch (PDOException $e) {
            $erros[] = "Erro ao salvar as alterações: " . $e->getMessage();
        }
    }
}

// Busca os dados atuais do usuário para preencher o formulário
try {
    $stmt = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id_personalizado = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        die("Usuário não encontrado.");
    }
} catch (PDOException $e) {
    die("Erro ao buscar dados do usuário: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/cadastro.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <main>
        <div class="container" id="cadastro-container">
            <h1>Editar Usuário</h1>
            <p class="subtitle">Altere as informações abaixo. Para redefinir a senha, preencha os campos de nova senha.</p>

            <?php if (!empty($erros)): ?>
                <div class="error-message">
                    <?php foreach ($erros as $erro): ?>
                        <p><?php echo htmlspecialchars($erro); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="editar_usuario.php?id=<?php echo htmlspecialchars($id_usuario); ?>">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($_POST['nome'] ?? $usuario['nome']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $usuario['email']); ?>" required>
                </div>
                
                <hr>
                
                <p class="subtitle" style="text-align: left; margin-bottom: 1rem; font-weight: bold;">Redefinir Senha (opcional)</p>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nova_senha">Nova Senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirmar_nova_senha">Confirme a Nova Senha</label>
                        <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" minlength="6">
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="admin_senha">Sua Senha de Admin (para confirmar a ação)</label>
                    <input type="password" id="admin_senha" name="admin_senha" required>
                </div>

                <div class="button-group">
                    <a href="admin_home.php" class="cancel-button" style="text-decoration: none; padding: 12px 25px; text-align: center;">Cancelar</a>
                    <button type="submit" class="register-button">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>