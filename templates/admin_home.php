<?php
// Em Dindinweb/templates/admin_home.php
require_once('../PHP/config.php');

// GUARDA DE SEGURANÇA: Garante que apenas administradores acessem
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/templates/homeComLogin.php');
    exit();
}

// Limpa a flag de "Ver como Usuário" ao voltar para o painel de admin
if (isset($_GET['exit_view_mode'])) {
    unset($_SESSION['view_as_user']);
}

try {
    // --- Dados para os Cards ---
    $total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $total_co2 = $pdo->query("SELECT SUM(co2_evitado) FROM estatisticas_usuario")->fetchColumn();
    $total_saldo_ddv = $pdo->query("SELECT SUM(saldo_ddv) FROM estatisticas_usuario")->fetchColumn();

    // --- Dados para o Gráfico de Cadastros ---
    $sql_grafico_cadastros = "SELECT YEAR(data_cadastro) as ano, MONTH(data_cadastro) as mes, COUNT(*) as total FROM usuarios GROUP BY ano, mes ORDER BY ano, mes LIMIT 12";
    $stmt_cadastros = $pdo->query($sql_grafico_cadastros);
    $dados_cadastros = $stmt_cadastros->fetchAll();
    
    $labels_cadastros = [];
    $data_cadastros = [];
    foreach ($dados_cadastros as $dado) {
        $labels_cadastros[] = date('M/y', mktime(0, 0, 0, $dado['mes'], 1, $dado['ano']));
        $data_cadastros[] = $dado['total'];
    }

    // --- Dados para o Gráfico de Itens Reciclados (Top 10) ---
    $sql_grafico_itens = "SELECT u.nome, e.itens_reciclados 
                          FROM usuarios u 
                          JOIN estatisticas_usuario e ON u.id_personalizado = e.usuario_id_personalizado 
                          WHERE e.itens_reciclados > 0 
                          ORDER BY e.itens_reciclados DESC 
                          LIMIT 10";
    $stmt_itens = $pdo->query($sql_grafico_itens);
    $dados_itens = $stmt_itens->fetchAll();

    $labels_itens = [];
    $data_itens = [];
    foreach ($dados_itens as $dado) {
        $labels_itens[] = $dado['nome'];
        $data_itens[] = $dado['itens_reciclados'];
    }
    
    // --- Dados para o Gráfico de Recompensas Mais Populares ---
    $sql_recompensas = "SELECT r.nome, COUNT(rr.id) as total_resgates
                        FROM recompensas_resgatadas rr
                        JOIN recompensas r ON rr.recompensa_id = r.id
                        GROUP BY r.nome
                        ORDER BY total_resgates DESC
                        LIMIT 5";
    $stmt_recompensas = $pdo->query($sql_recompensas);
    $dados_recompensas = $stmt_recompensas->fetchAll();

    $labels_recompensas = [];
    $data_recompensas = [];
    foreach ($dados_recompensas as $dado) {
        $labels_recompensas[] = $dado['nome'];
        $data_recompensas[] = $dado['total_resgates'];
    }
    
    // --- Lista de todos os usuários para a tabela ---
    $stmt_usuarios = $pdo->query("SELECT id_personalizado, nome, email, data_cadastro, is_admin FROM usuarios ORDER BY data_cadastro DESC");
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
    <title>Dashboard - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <?php include '../includes/header_admin.php'; ?>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Dashboard do Administrador</h1>
            <p class="subtitle">Visão geral e gerenciamento do sistema DinDin Verde.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon users"><i class="fas fa-users"></i></div>
                <div class="info">
                    <strong><?php echo $total_usuarios; ?></strong>
                    <span>Usuários Cadastrados</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon co2"><i class="fas fa-smog"></i></div>
                <div class="info">
                    <strong><?php echo number_format($total_co2 ?? 0, 1, ','); ?> kg</strong>
                    <span>Total de CO₂ Evitado</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon money"><i class="fas fa-wallet"></i></div>
                <div class="info">
                    <strong><?php echo number_format($total_saldo_ddv ?? 0, 2, ',', '.'); ?> DDV</strong>
                    <span>Saldo em Circulação</span>
                </div>
            </div>
        </div>

        <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--color-border);">

        <div class="grid-2" style="gap: 2rem; align-items: flex-start; margin-bottom: 2rem;">
            <div class="user-management">
                <h2>Novos Cadastros por Mês</h2>
                <div class="grafico-container">
                    <canvas id="cadastrosChart"></canvas>
                </div>
            </div>
            <div class="user-management">
                <h2>Top 10 Recicladores (por itens)</h2>
                <div class="grafico-container">
                    <canvas id="itensRecicladosChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="user-management">
            <h2>Recompensas Mais Populares (Top 5)</h2>
            <div class="grafico-container">
                <canvas id="recompensasChart"></canvas>
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
                                <td><span class="status-<?php echo $usuario['is_admin'] ? 'admin' : 'user'; ?>"><?php echo $usuario['is_admin'] ? 'Admin' : 'Usuário'; ?></span></td>
                                <td class="admin-actions">
                                    <a href="perfil.php?id=<?php echo urlencode($usuario['id_personalizado']); ?>" title="Ver Perfil"><i class="fas fa-eye"></i></a>
                                    <a href="editar_usuario.php?id=<?php echo urlencode($usuario['id_personalizado']); ?>" title="Editar Usuário"><i class="fas fa-edit"></i></a>
                                    <a href="confirmar_acao.php?acao=tornar_admin&id=<?php echo urlencode($usuario['id_personalizado']); ?>" title="Alterar Status"><i class="fas fa-user-shield"></i></a>
                                    <a href="confirmar_acao.php?acao=excluir&id=<?php echo urlencode($usuario['id_personalizado']); ?>" onclick="return confirm('Tem certeza que deseja prosseguir para a tela de confirmação de exclusão?');" title="Excluir Usuário" class="delete"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">Nenhum usuário cadastrado ainda.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        const DADOS_GRAFICO_CADASTROS = {
            labels: <?php echo json_encode($labels_cadastros); ?>,
            data:   <?php echo json_encode($data_cadastros); ?>
        };
        const DADOS_GRAFICO_ITENS = {
            labels: <?php echo json_encode($labels_itens); ?>,
            data:   <?php echo json_encode($data_itens); ?>
        };
        // CORREÇÃO: Passando os dados do novo gráfico para o JavaScript
        const DADOS_GRAFICO_RECOMPENSAS = {
            labels: <?php echo json_encode($labels_recompensas); ?>,
            data:   <?php echo json_encode($data_recompensas); ?>
        };
    </script>
    
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/admin.js"></script>
</body>
</html>