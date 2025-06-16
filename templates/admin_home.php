<?php
// Em Dindinweb/templates/admin_home.php
// Inclui o config e os helpers
require_once('../PHP/config.php');
require_once('../PHP/helpers.php');

// GUARDA DE SEGURANÇA: Garante que apenas administradores acessem
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/templates/homeComLogin.php');
    exit();
}

// --- Busca de Dados para o Dashboard ---
try {
    // Total de usuários
    $total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    
    // Total de CO2 evitado
    $total_co2 = $pdo->query("SELECT SUM(co2_evitado) FROM estatisticas_usuario")->fetchColumn();
    
    // Total de saldo em circulação
    $total_saldo_ddv = $pdo->query("SELECT SUM(saldo_ddv) FROM estatisticas_usuario")->fetchColumn();

    // Lista de todos os usuários para a tabela
    $stmt_usuarios = $pdo->query("SELECT id, id_personalizado, nome, email, data_cadastro, is_admin FROM usuarios ORDER BY data_cadastro DESC");
    $todos_usuarios = $stmt_usuarios->fetchAll();

} catch (PDOException $e) {
    die("Erro ao buscar dados para o dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/perfil.css">
    <style>
        .admin-header { background-color: #343a40; }
        .admin-header .logo, .admin-header .nav-links a, .admin-header .dropdown-toggle { color: #fff; }
        .admin-header .logo-icon { color: var(--color-primary); }
        .admin-header .nav-links a:hover { color: #ddd; border-bottom-color: var(--color-primary); }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 2rem; background: #fff; box-shadow: var(--shadow-md); border-radius: var(--border-radius); overflow: hidden; }
        .admin-table th, .admin-table td { padding: 1rem; border-bottom: 1px solid var(--color-border); text-align: left; }
        .admin-table th { background-color: var(--color-gray-light); font-weight: 600; }
        .admin-table td.admin-actions a { color: var(--color-text-light); margin-right: 15px; font-size: 1rem; }
        .admin-table td.admin-actions a:hover { color: var(--color-primary-dark); }
        .admin-table td.admin-actions a.delete:hover { color: var(--color-red); }
        .admin-table tbody tr:last-child td { border-bottom: none; }
        .admin-table tbody tr:hover { background-color: #f9f9f9; }
    </style>
</head>
<body>
    
    <?php include '../includes/header_admin.php'; ?>

    <main class="container" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="admin-header">
            <h1>Dashboard do Administrador</h1>
            <p class="subtitle">Visão geral e gerenciamento do sistema DinDin Verde.</p>
        </div>

        <div class="grid-3">
            <div class="stat-box">
                <strong><?php echo $total_usuarios; ?></strong>
                <span>Usuários Cadastrados</span>
            </div>
            <div class="stat-box">
                <strong><?php echo number_format($total_co2 ?? 0, 1, ','); ?> kg</strong>
                <span>Total de CO2 Evitado</span>
            </div>
            <div class="stat-box">
                <strong><?php echo number_format($total_saldo_ddv ?? 0, 2, ',', '.'); ?> DDV</strong>
                <span>Saldo em Circulação</span>
            </div>
        </div>

        <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--color-border);">

        <div class="user-management">
            <h2>Gerenciamento de Usuários</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID Personalizado</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($todos_usuarios) > 0): ?>
                        <?php foreach ($todos_usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id_personalizado']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><?php echo $usuario['is_admin'] ? '<strong>Admin</strong>' : 'Usuário'; ?></td>
                                <td class="admin-actions">
                                    <a href="perfil.php?id=<?php echo $usuario['id']; ?>" title="Ver Perfil"><i class="fas fa-eye"></i></a>
                                    <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" title="Editar Usuário"><i class="fas fa-edit"></i></a>
                                    <a href="tornar_admin.php?id=<?php echo $usuario['id']; ?>" title="Tornar Admin/Usuário"><i class="fas fa-user-shield"></i></a>
                                    <a href="excluir_usuario.php?id=<?php echo $usuario['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.');" title="Excluir Usuário" class="delete"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Nenhum usuário cadastrado ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>