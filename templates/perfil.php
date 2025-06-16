<?php
require_once('../PHP/config.php');
require_once('../PHP/helpers.php');

// O "guarda" de segurança
if (!isset($_SESSION['usuario_id'])) { 
    header('Location: ' . BASE_URL . '/templates/login.php'); 
    exit(); 
}

// --- Busca os dados do perfil do banco de dados ---
try {
    // A consulta agora usa u.id para a junção e o filtro
    $sql = "SELECT u.id_personalizado, u.data_cadastro, u.foto_perfil, e.* FROM usuarios u
            LEFT JOIN estatisticas_usuario e ON u.id = e.id_usuario
            WHERE u.id = :id_usuario";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_usuario' => $_SESSION['usuario_id']]);
    $usuario_completo = $stmt->fetch();

    if (!$usuario_completo) {
        $stmt_user = $pdo->prepare("SELECT id_personalizado, data_cadastro, foto_perfil FROM usuarios WHERE id = :id_usuario");
        $stmt_user->execute([':id_usuario' => $_SESSION['usuario_id']]);
        $usuario_base = $stmt_user->fetch();
        // Cria um array com valores padrão para não dar erro na página
        $usuario_completo = array_merge($usuario_base, [
            'itens_reciclados' => 0,
            'mercados_visitados' => 0,
            'meses_consecutivos' => 0,
            'nivel' => 'Reciclador Iniciante',
            'progresso_nivel' => 0,
            'co2_evitado' => 0,
            'agua_economizada' => 0,
            'energia_poupada' => 0,
            'saldo_ddv' => 0,
            'saldo_processamento' => 0,
            'saldo_total_acumulado' => 0
        ]);
    }

    // Calcula o total de meses como membro dinamicamente
    $dataCadastro = new DateTime($usuario_completo['data_cadastro']);
    $dataAtual = new DateTime('now');
    $diferenca = $dataCadastro->diff($dataAtual);
    $mesesComoMembro = ($diferenca->y * 12) + $diferenca->m;
    if ($diferenca->days > 0 && $mesesComoMembro == 0) {
        $mesesComoMembro = 1; 
    }
    
    // A pontuação agora é calculada com base nas estatísticas
    $pontuacao_atual = ($usuario_completo['co2_evitado'] ?? 0) + ($usuario_completo['agua_economizada'] ?? 0) + ($usuario_completo['energia_poupada'] ?? 0);
    $proximoNivelTexto = '';
    // Usa as funções do helpers.php
    $nivel_usuario = determinarNivel($pontuacao_atual);
    $progresso_percentual = (float) calcularProgresso($pontuacao_atual, $proximoNivelTexto);

} catch (PDOException $e) {
    die("Erro ao buscar dados do perfil: " . $e->getMessage());
}
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <?php include '../includes/header_logado.php'; ?>

    <div class="perfil-container">
        <aside class="perfil-lateral">
            <div class="perfil-header">
                <div class="perfil-avatar-upload">
                    <img src="../PHP/exibir_foto.php" alt="Foto de Perfil" class="perfil-foto">
                </div>
                <h2><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></h2>
                <p>Membro desde <?php echo date('M/Y', strtotime($usuario_completo['data_cadastro'])); ?></p>
            </div>
            
            <div class="upload-form">
                <form action="../PHP/upload_foto.php" method="post" enctype="multipart/form-data">
                    <label for="foto" class="btn-upload">Trocar Foto</label>
                    <input type="file" name="foto_perfil" id="foto" required style="display: none;" onchange="this.form.submit()">
                </form>
            </div>

            <div class="perfil-stats">
                <div><strong><?php echo htmlspecialchars($usuario_completo['itens_reciclados']); ?></strong><span>Itens</span></div>
                <div><strong><?php echo htmlspecialchars($usuario_completo['mercados_visitados']); ?></strong><span>Mercados</span></div>
                <div><strong><?php echo $mesesComoMembro; ?></strong><span>Meses</span></div>
            </div>
            <div class="perfil-nivel" data-pontuacao="<?php echo $pontuacao_atual; ?>">
                <p>Nível: <strong id="nivel-texto"><?php echo htmlspecialchars($nivel_usuario); ?></strong></p>
                <div class="progress-bar">
                    <div id="progresso-barra" style="width: <?php echo $progresso_percentual; ?>%;"></div>
                </div>
                <small id="progresso-texto"><?php echo htmlspecialchars($proximoNivelTexto); ?></small>
            </div>
            <div class="perfil-recompensas">
                <h3>Minhas Recompensas</h3>
                <div class="recompensa-item"><i class="fas fa-tag"></i><span><strong>10% de desconto</strong><br>No Mercado Orgânico</span></div>
                <div class="recompensa-item"><i class="fas fa-ticket-alt"></i><span><strong>Ingresso cortesia</strong><br>Para o Parque Ecológico</span></div>
            </div>
            <a href="#" class="btn">Ver todas as recompensas</a>
        </aside>

        <main class="conteudo-principal">
            <div class="tabs">
                <button class="tab-button active" data-target="#meu-impacto">Meu Impacto</button>
                <button class="tab-button" data-target="#meu-dindin">Meu DinDin</button>
            </div>
            <div id="meu-impacto" class="tab-content active">
                <h3>Seu impacto ambiental positivo</h3>
                <div class="grid-3">
                    <div class="stat-box"><strong><?php echo htmlspecialchars(number_format($usuario_completo['co2_evitado'], 1, ',')); ?> kg</strong><span>CO2 evitado</span></div>
                    <div class="stat-box"><strong><?php echo htmlspecialchars(number_format($usuario_completo['agua_economizada'], 0, '.', '.')); ?> L</strong><span>Água economizada</span></div>
                    <div class="stat-box"><strong><?php echo htmlspecialchars($usuario_completo['energia_poupada']); ?> kWh</strong><span>Energia poupada</span></div>
                </div>
                <h3>Suas estatísticas mensais</h3>
                <div class="grafico-container" style="position: relative; height:300px; width:100%;">
                    <canvas id="impactoChart"></canvas>
                </div>
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
                        <strong><?php echo number_format($usuario_completo['saldo_ddv'], 2, ',', '.'); ?> <small>DDV</small></strong>
                        <a href="#" class="btn">Resgatar</a>
                    </div>
                    <hr>
                    <div class="saldo-detalhes">
                        <div class="saldo-item"><span>Valor disponível</span><strong><?php echo number_format($usuario_completo['saldo_ddv'], 2, ',', '.'); ?> DDV</strong></div>
                        <div class="saldo-item"><span>Em processamento</span><strong><?php echo number_format($usuario_completo['saldo_processamento'], 2, ',', '.'); ?> DDV</strong></div>
                        <div class="saldo-item"><span>Total acumulado</span><strong><?php echo number_format($usuario_completo['saldo_total_acumulado'], 2, ',', '.'); ?> DDV</strong></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        const DADOS_PERFIL = {
            co2: <?php echo $usuario_completo['co2_evitado'] ?? 0; ?>,
            agua: <?php echo $usuario_completo['agua_economizada'] ?? 0; ?>,
            energia: <?php echo $usuario_completo['energia_poupada'] ?? 0; ?>
        };
    </script>
    
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/perfil.js"></script>
</body>
</html>