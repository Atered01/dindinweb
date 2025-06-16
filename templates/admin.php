<?php
// Em Dindinweb/templates/admin.php
require_once('../PHP/config.php');

// GUARDA DE SEGURANÇA:
// Se o usuário não estiver logado OU não for admin, expulsa para a home.
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/templates/homeComLogin.php');
    exit();
}

// Busca todos os usuários do banco para exibir na tabela
try {
    $stmt = $pdo->query("SELECT id, id_personalizado, nome, email, data_cadastro FROM usuarios ORDER BY data_cadastro DESC");
    $todos_usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar usuários: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração - DinDin Verde</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <style>
        .admin-container { padding: 2rem; max-width: 1200px; margin: auto; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 2rem; background: #fff; box-shadow: var(--shadow-md); }
        .admin-table th, .admin-table td { padding: 0.75rem 1rem; border: 1px solid var(--color-border); text-align: left; }
        .admin-table th { background-color: var(--color-gray-light); }
    </style>
</head>
<body>
    <?php include '../includes/header_logado.php'; ?>

    <main class="admin-container">
        <h1>Painel de Administração</h1>
        <p>Gerenciamento de usuários do sistema.</p>

        <section>
            <h2>Usuários Cadastrados</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ID Personalizado</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Data de Cadastro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todos_usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['id_personalizado']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_cadastro'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>