<?php
// Em Dindinweb/templates/painel_b2b.php
require_once('../PHP/config.php');

if (!isset($_SESSION['empresa_id'])) {
    header('Location: empresa_login.php');
    exit();
}
$empresa_id = $_SESSION['empresa_id'];

try {
    $stmt_kpi = $pdo->prepare("SELECT COUNT(d.id) as total_descartes, COUNT(DISTINCT d.usuario_id_personalizado) as clientes_engajados, SUM(p.co2_evitado) as total_co2, SUM(p.pontos_ddv) as total_ddv FROM descartes d JOIN produtos p ON d.produto_id = p.id WHERE p.empresa_id = ?");
    $stmt_kpi->execute([$empresa_id]);
    $kpi = $stmt_kpi->fetch();

    $stmt_produtos = $pdo->prepare("SELECT p.nome_produto, COUNT(d.id) as total FROM descartes d JOIN produtos p ON d.produto_id = p.id WHERE p.empresa_id = ? GROUP BY p.nome_produto ORDER BY total DESC LIMIT 5");
    $stmt_produtos->execute([$empresa_id]);
    $produtos_populares = $stmt_produtos->fetchAll();

    $labels_produtos = [];
    $data_produtos = [];
    foreach ($produtos_populares as $prod) {
        $labels_produtos[] = $prod['nome_produto'];
        $data_produtos[] = $prod['total'];
    }

} catch (PDOException $e) {
    die("Erro ao carregar dados do painel: " . $e->getMessage());
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin.css"> <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/painel_b2b.css"> <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <?php include '../includes/header_empresa.php'; // Incluindo o novo cabeçalho ?>

    <main class="admin-container">
        <h1>Painel de Impacto</h1>
        <p class="subtitle">Acompanhe o ciclo de vida e o impacto positivo das suas embalagens.</p>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="info"><strong><?php echo number_format($kpi['total_descartes'] ?? 0); ?></strong><span>Embalagens Recicladas</span></div>
            </div>
            <div class="stat-card">
                <div class="info"><strong><?php echo number_format($kpi['clientes_engajados'] ?? 0); ?></strong><span>Clientes Engajados</span></div>
            </div>
            <div class="stat-card">
                <div class="info"><strong><?php echo number_format($kpi['total_co2'] ?? 0, 2, ',', '.'); ?> kg</strong><span>CO₂ Evitado</span></div>
            </div>
            <div class="stat-card">
                <div class="info"><strong><?php echo number_format($kpi['total_ddv'] ?? 0, 0, '.', '.'); ?></strong><span>DDV Gerados</span></div>
            </div>
        </div>

        <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--color-border);">

        <div class="user-management">
            <h2>Seus Produtos Mais Reciclados</h2>
            <div class="grafico-container" style="height: 400px; position: relative;">
                <canvas id="produtosChart"></canvas>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        const DADOS_PRODUTOS = {
            labels: <?php echo json_encode($labels_produtos); ?>,
            data:   <?php echo json_encode($data_produtos); ?>
        };

        const ctx = document.getElementById('produtosChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: DADOS_PRODUTOS.labels,
                datasets: [{
                    label: 'Total de Descartados',
                    data: DADOS_PRODUTOS.data,
                    backgroundColor: 'rgba(46, 125, 50, 0.7)',
                    borderColor: 'rgba(46, 125, 50, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                 scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>