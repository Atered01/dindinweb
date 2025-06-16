<?php
// Em Dindinweb/templates/editar_usuario.php
require_once('../PHP/config.php');

// Guarda de segurança do admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$id_usuario = $_GET['id'] ?? null;
if (!$id_usuario) die("ID não fornecido.");

// Lógica para salvar as alterações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pegar os dados do formulário e executar um UPDATE
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    // ... adicione outros campos que queira editar

    $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $email, $id_usuario]);

    header('Location: admin_home.php');
    exit();
}

// Busca os dados atuais do usuário para preencher o formulário
try {
    $stmt = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();
    if (!$usuario) die("Usuário não encontrado.");
} catch (PDOException $e) {
    die("Erro ao buscar usuário: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/cadastro.css">
</head>
<body>
    <?php include '../includes/header_admin.php'; ?>
    <main>
        <div class="container" id="cadastro-container">
            <h1>Editar Usuário</h1>
            <form method="POST" action="editar_usuario.php?id=<?php echo $id_usuario; ?>">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>
                <div class="button-group">
                    <button type="submit" class="register-button">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>