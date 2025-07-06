<?php
// Em Dindinweb/templates/painel_b2b.php
require_once('../PHP/config.php');

if (!isset($_SESSION['empresa_id'])) {
    header('Location: empresa_login.php');
    exit();
}

$gemini_api_key = getenv('GEMINI_API_KEY');

$empresa_id = $_SESSION['empresa_id'];

// --- LÓGICA DOS FILTROS ---
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-90 days'));
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');
$produto_id_filtro = $_GET['produto_id'] ?? 'todos';
$nivel_usuario_filtro = $_GET['nivel_usuario'] ?? 'todos';
$estado_filtro = $_GET['estado'] ?? 'todos';


// --- CONSTRUÇÃO DAS CLÁUSULAS WHERE DINÂMICAS ---
$params = [$empresa_id];
$clausula_where = "p.empresa_id = ?";
$join_estatisticas = "";
$join_usuarios = "";

$clausula_where .= " AND d.data_descarte BETWEEN ? AND ?";
$params[] = $data_inicio;
$params[] = $data_fim . ' 23:59:59';

if ($produto_id_filtro !== 'todos') {
    $clausula_where .= " AND p.id = ?";
    $params[] = $produto_id_filtro;
}

if ($nivel_usuario_filtro !== 'todos') {
    $join_estatisticas = " JOIN estatisticas_usuario e ON d.usuario_id_personalizado = e.usuario_id_personalizado ";
    $clausula_where .= " AND
        CASE
            WHEN e.saldo_total_acumulado >= 30000 THEN 'Reciclador Ouro'
            WHEN e.saldo_total_acumulado >= 15000 THEN 'Reciclador Prata'
            WHEN e.saldo_total_acumulado >= 5000 THEN 'Reciclador Bronze'
            ELSE 'Reciclador Iniciante'
        END = ?";
    $params[] = $nivel_usuario_filtro;
}

if ($estado_filtro !== 'todos') {
    $join_usuarios = " JOIN usuarios u ON d.usuario_id_personalizado = u.id_personalizado ";
    $clausula_where .= " AND u.estado = ?";
    $params[] = $estado_filtro;
}


try {
    // --- DADOS PARA OS DROPDOWNS DOS FILTROS ---
    $produtos_da_empresa = $pdo->prepare("SELECT id, nome_produto FROM produtos WHERE empresa_id = ? ORDER BY nome_produto");
    $produtos_da_empresa->execute([$empresa_id]);
    $lista_produtos = $produtos_da_empresa->fetchAll();

    $estados_com_descarte = $pdo->prepare(
        "SELECT DISTINCT u.estado FROM descartes d 
         JOIN produtos p ON d.produto_id = p.id 
         JOIN usuarios u ON d.usuario_id_personalizado = u.id_personalizado 
         WHERE p.empresa_id = ? AND u.estado IS NOT NULL ORDER BY u.estado"
    );
    $estados_com_descarte->execute([$empresa_id]);
    $lista_estados = $estados_com_descarte->fetchAll(PDO::FETCH_COLUMN);

    $niveis_disponiveis = ['Reciclador Iniciante', 'Reciclador Bronze', 'Reciclador Prata', 'Reciclador Ouro'];

    // --- CONSULTAS ATUALIZADAS PARA USAR A CLÁUSULA DINÂMICA ---
    $sql_base = "FROM descartes d JOIN produtos p ON d.produto_id = p.id $join_usuarios $join_estatisticas WHERE $clausula_where";

    $stmt_kpi = $pdo->prepare("SELECT COUNT(d.id) as total_embalagens, COUNT(DISTINCT d.usuario_id_personalizado) as clientes_unicos $sql_base");
    $stmt_kpi->execute($params);
    $kpi = $stmt_kpi->fetch();

    $stmt_impacto = $pdo->prepare("SELECT SUM(p.co2_evitado) as total_co2, SUM(p.pontos_ddv) as total_ddv $sql_base");
    $stmt_impacto->execute($params);
    $impacto = $stmt_impacto->fetch();

    $stmt_tendencia = $pdo->prepare("SELECT DATE(d.data_descarte) as dia, COUNT(d.id) as total $sql_base GROUP BY dia ORDER BY dia ASC");
    $stmt_tendencia->execute($params);
    $tendencia_diaria = $stmt_tendencia->fetchAll();

    $stmt_produtos = $pdo->prepare("SELECT p.nome_produto, COUNT(d.id) as total $sql_base GROUP BY p.nome_produto ORDER BY total DESC LIMIT 10");
    $stmt_produtos->execute($params);
    $produtos_populares = $stmt_produtos->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar dados do painel: " . $e->getMessage());
}

$labels_tendencia = [];
$data_tendencia = [];
foreach ($tendencia_diaria as $row) {
    $labels_tendencia[] = date("d/m", strtotime($row['dia']));
    $data_tendencia[] = $row['total'];
}

$labels_produtos = [];
$data_produtos = [];
foreach ($produtos_populares as $prod) {
    $labels_produtos[] = $prod['nome_produto'];
    $data_produtos[] = $prod['total'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Empresa - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/painel_b2b.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <?php include '../includes/header_empresa.php'; ?>

    <main class="admin-container">
        <h1>Painel de Impacto</h1>
        <p class="subtitle">Acompanhe o ciclo de vida e o impacto positivo das suas embalagens.</p>

        <div class="filtro-dropdown">
            <button id="filtroBtn" class="btn btn-secondary"><i class="fas fa-filter"></i> Filtros</button>
            <div id="filtroContainer" class="filtro-container">
                <form method="GET" action="painel_b2b.php">
                    <div class="filtro-grid">
                        <div>
                            <label for="data_inicio">De:</label>
                            <input type="date" name="data_inicio" id="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>">
                        </div>
                        <div>
                            <label for="data_fim">Até:</label>
                            <input type="date" name="data_fim" id="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>">
                        </div>
                        <div class="filtro-item-largo">
                            <label for="produto_id">Produto:</label>
                            <select name="produto_id" id="produto_id">
                                <option value="todos">Todos os Produtos</option>
                                <?php foreach ($lista_produtos as $produto): ?>
                                    <option value="<?php echo $produto['id']; ?>" <?php echo ($produto_id_filtro == $produto['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($produto['nome_produto']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="nivel_usuario">Nível do Usuário:</label>
                            <select name="nivel_usuario" id="nivel_usuario">
                                <option value="todos">Todos os Níveis</option>
                                <?php foreach ($niveis_disponiveis as $nivel): ?>
                                    <option value="<?php echo $nivel; ?>" <?php echo ($nivel_usuario_filtro == $nivel) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($nivel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="estado">Estado:</label>
                            <select name="estado" id="estado">
                                <option value="todos">Todos os Estados</option>
                                <?php foreach ($lista_estados as $estado): ?>
                                    <option value="<?php echo $estado; ?>" <?php echo ($estado_filtro == $estado) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($estado); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filtro-actions">
                        <a href="painel_b2b.php" class="btn-limpar">Limpar Filtros</a>
                        <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="info"><strong><?php echo number_format($kpi['total_embalagens'] ?? 0); ?></strong><span>Embalagens Recicladas</span></div>
            </div>
            <div class="stat-card">
                <div class="info"><strong><?php echo number_format($impacto['total_co2'] ?? 0, 2, ',', '.'); ?> kg</strong><span>CO₂ Evitado</span></div>
            </div>
            <div class="stat-card">
                <div class="info"><strong><?php echo number_format($kpi['clientes_unicos'] ?? 0); ?></strong><span>Clientes Engajados</span></div>
            </div>
            <div class="stat-card">
                <div class="info"><strong><?php echo number_format($impacto['total_ddv'] ?? 0, 0, '.', '.'); ?></strong><span>DDV Gerados</span></div>
            </div>
        </div>

        <div class="user-management" style="margin-top: 2rem;">
            <h2>Tendência de Reciclagem no Período</h2>
            <div class="grafico-container" style="height: 350px;"><canvas id="tendenciaChart"></canvas></div>
        </div>

        <div class="user-management" style="margin-top: 2rem;">
            <h2>Produtos Mais Reciclados no Período</h2>
            <div class="grafico-container" style="height: 400px;"><canvas id="produtosChart"></canvas></div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.getElementById('filtroBtn').addEventListener('click', function() {
            document.getElementById('filtroContainer').classList.toggle('show');
        });

        window.addEventListener('click', function(e) {
            const filtroContainer = document.getElementById('filtroContainer');
            const filtroBtn = document.getElementById('filtroBtn');
            if (!filtroBtn.contains(e.target) && !filtroContainer.contains(e.target)) {
                filtroContainer.classList.remove('show');
            }
        });

        const DADOS_TENDENCIA = {
            labels: <?php echo json_encode($labels_tendencia); ?>,
            data: <?php echo json_encode($data_tendencia); ?>
        };
        const DADOS_PRODUTOS = {
            labels: <?php echo json_encode($labels_produtos); ?>,
            data: <?php echo json_encode($data_produtos); ?>
        };
        const ctxTendencia = document.getElementById('tendenciaChart').getContext('2d');
        new Chart(ctxTendencia, {
            type: 'line',
            data: {
                labels: DADOS_TENDENCIA.labels,
                datasets: [{
                    label: 'Embalagens Recicladas',
                    data: DADOS_TENDENCIA.data,
                    backgroundColor: 'rgba(46, 125, 50, 0.1)',
                    borderColor: 'rgba(46, 125, 50, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        const ctxProdutos = document.getElementById('produtosChart').getContext('2d');
        new Chart(ctxProdutos, {
            type: 'bar',
            data: {
                labels: DADOS_PRODUTOS.labels,
                datasets: [{
                    label: 'Total Descartado',
                    data: DADOS_PRODUTOS.data,
                    backgroundColor: 'rgba(66, 165, 245, 0.7)',
                    borderColor: 'rgba(25, 118, 210, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>

</html>