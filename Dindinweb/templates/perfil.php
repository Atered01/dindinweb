<?php
session_start();

// CORREÇÃO: Incluindo o config.php no topo para definir a BASE_URL
require_once('../PHP/config.php');

// O "guarda" de segurança que você já tinha
if (!isset($_SESSION['usuario_id'])) { 
    header('Location: ' . BASE_URL . '../templates/login.php'); 
    exit(); 
}

// Incluindo os dados simulados do usuário
require_once('../data/user_data.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/perfil.css">
</head>
<body>
    
    <?php include '../includes/header_logado.php'; ?>

    <div class="perfil-container">
        <aside class="perfil-lateral">
            <div class="perfil-header">
                <div class="perfil-avatar"><i class="fas fa-user"></i></div>
                <h2><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></h2>
                <p>Membro desde <?php echo htmlspecialchars($user['member_since']); ?></p>
            </div>
            <div class="perfil-stats">
                <div>
                    <strong><?php echo htmlspecialchars($user['recycled_items']); ?></strong>
                    <span>Itens reciclados</span>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($user['markets']); ?></strong>
                    <span>Mercados</span>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($user['consecutive_months']); ?></strong>
                    <span>Meses seguidos</span>
                </div>
            </div>
            <div class="perfil-nivel">
                <p>Nível: <strong><?php echo htmlspecialchars($user['level']); ?></strong></p>
                <div class="progress-bar">
                    <div style="width: <?php echo htmlspecialchars($user['progress_to_next_level']); ?>%;"></div>
                </div>
                <small>Progresso para Prata</small>
            </div>
            <div class="perfil-recompensas">
                <h3>Minhas Recompensas</h3>
                <div class="recompensa-item">
                    <i class="fas fa-tag"></i>
                    <span><strong>10% de desconto</strong><br>No Mercado Orgânico</span>
                </div>
                <div class="recompensa-item">
                    <i class="fas fa-ticket-alt"></i>
                    <span><strong>Ingresso cortesia</strong><br>Para o Parque Ecológico</span>
                </div>
            </div>
            <a href="#" class="btn">Ver todas as recompensas</a>
        </aside>

        <main class="conteudo-principal">
            <div class="tabs">
                <button class="tab-button active" data-target="#meu-impacto">Meu Impacto</button>
                <button class="tab-button" data-target="#meu-dindin">Meu DinDin</button>
                <button class="tab-button" data-target="#configuracoes">Configurações</button>
            </div>

            <div id="meu-impacto" class="tab-content active">
                <h3>Seu impacto ambiental positivo</h3>
                <div class="grid-3">
                    <div class="stat-box"><strong>42,5 kg</strong><span>CO2 evitado</span></div>
                    <div class="stat-box"><strong>12.360 L</strong><span>Água economizada</span></div>
                    <div class="stat-box"><strong>158 kWh</strong><span>Energia poupada</span></div>
                </div>
                <h3>Suas estatísticas mensais</h3>
                <div class="grafico-placeholder"><i class="fas fa-chart-line"></i> Gráfico de reciclagem mensal</div>
                <h3>Seus certificados</h3>
                <div class="grid-2">
                    <div class="certificado-card"><strong>Certificado Bronze</strong><p>Por reciclar mais de 100 itens em 3 meses consecutivos.</p><a href="#">Baixar certificado</a></div>
                    <div class="certificado-card"><strong>Embaixador Verde</strong><p>Por indicar 5 amigos que se tornaram recicladores ativos.</p><a href="#">Compartilhar</a></div>
                </div>
            </div>

            <div id="meu-dindin" class="tab-content">
                <h3>Seu saldo DinDin Verde</h3>
                <div class="saldo-box">
                    <div class="saldo-principal">
                        <strong><?php echo number_format($user['balance'], 2, ',', '.'); ?> <small>DDV</small></strong>
                        <a href="#" class="btn">Resgatar</a>
                    </div>
                    <hr>
                    <div class="saldo-detalhes">
                        <div class="saldo-item"><span>Valor disponível</span><strong><?php echo number_format($user['balance'], 2, ',', '.'); ?> DDV</strong></div>
                        <div class="saldo-item"><span>Em processamento</span><strong><?php echo number_format($user['processing_balance'], 2, ',', '.'); ?> DDV</strong></div>
                        <div class="saldo-item"><span>Total acumulado</span><strong><?php echo number_format($user['total_balance'], 2, ',', '.'); ?> DDV</strong></div>
                    </div>
                </div>
            </div>
            
            <div id="configuracoes" class="tab-content">
                <h3>Configurações da Conta</h3>
                <p>Área para alterar dados pessoais, senha e preferências de notificação.</p>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/perfil.js"></script>
</body>
</html>