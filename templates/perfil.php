<?php
require_once('../PHP/config.php');
require_once('../PHP/helpers.php');

// O "guarda" de segurança
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

// Lógica para decidir qual perfil exibir
$id_usuario_para_exibir = $_SESSION['usuario_id'];
$perfil_proprio = true;

if (isset($_GET['id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    $id_selecionado = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id_selecionado) {
        $id_usuario_para_exibir = $id_selecionado;
        $perfil_proprio = ($id_usuario_para_exibir == $_SESSION['usuario_id']);
    }
}

// --- Busca os dados do perfil do banco de dados ---
try {
    $sql = "SELECT u.*, e.* FROM usuarios u
            LEFT JOIN estatisticas_usuario e ON u.id = e.id_usuario
            WHERE u.id = :id_usuario";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_usuario' => $id_usuario_para_exibir]);
    $usuario_completo = $stmt->fetch();

    if (!$usuario_completo) {
        die("Usuário não encontrado.");
    }

    if (!isset($usuario_completo['id_usuario'])) {
        $usuario_completo = array_merge($usuario_completo, [
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

    $dataCadastro = new DateTime($usuario_completo['data_cadastro']);
    $dataAtual = new DateTime('now');
    $diferenca = $dataCadastro->diff($dataAtual);
    $mesesComoMembro = ($diferenca->y * 12) + $diferenca->m;
    if ($diferenca->days >= 0 && $mesesComoMembro == 0) $mesesComoMembro = 1;

    $pontuacao_atual = ($usuario_completo['co2_evitado'] ?? 0) + ($usuario_completo['agua_economizada'] ?? 0) + ($usuario_completo['energia_poupada'] ?? 0);
    $proximoNivelTexto = '';
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
    <title>Perfil de <?php echo htmlspecialchars($usuario_completo['nome']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/perfil.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <?php include '../includes/header_logado.php'; ?>

    <div class="perfil-container">
        <aside class="perfil-lateral">
            <div class="perfil-header">
                <div class="perfil-avatar-upload">
                    <img src="../PHP/exibir_foto.php?id=<?php echo $id_usuario_para_exibir; ?>" alt="Foto de Perfil" class="perfil-foto">
                </div>
                <h2><?php echo htmlspecialchars($usuario_completo['nome']); ?></h2>
                <p>Membro desde <?php echo date('M/Y', strtotime($usuario_completo['data_cadastro'])); ?></p>
            </div>

            <div class="perfil-stats">
                <div><strong><?php echo htmlspecialchars($usuario_completo['itens_reciclados']); ?></strong><span>Itens</span></div>
                <div><strong><?php echo htmlspecialchars($usuario_completo['mercados_visitados']); ?></strong><span>Mercados</span></div>
                <div><strong><?php echo $mesesComoMembro; ?></strong><span>Meses</span></div>
            </div>

            <div class="perfil-nivel">
                <p>Nível: <strong id="nivel-texto"><?php echo htmlspecialchars($nivel_usuario); ?></strong></p>
                <div class="progress-bar">
                    <div id="progresso-barra" style="width: <?php echo $progresso_percentual; ?>%;"></div>
                </div>
                <small id="progresso-texto"><?php echo htmlspecialchars($proximoNivelTexto); ?></small>
            </div>

            <?php if ($perfil_proprio): ?>
                <div class="perfil-recompensas">
                    <h3>Minhas Recompensas</h3>
                    <div class="recompensa-item"><i class="fas fa-tag"></i><span><strong>10% de desconto</strong><br>No Mercado Orgânico</span></div>
                    <div class="recompensa-item"><i class="fas fa-ticket-alt"></i><span><strong>Ingresso cortesia</strong><br>Para o Parque Ecológico</span></div>
                    <a href="#" class="btn">Ver todas as recompensas</a>
                </div>
            <?php endif; ?>
        </aside>

        <main class="conteudo-principal">
            <div class="tabs">
                <button class="tab-button active" data-target="#meu-impacto">Impacto</button>
                <?php if ($perfil_proprio): ?>
                    <button class="tab-button" data-target="#meu-dindin">Meu DinDin</button>
                <?php endif; ?>
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
                    <?php if ($pontuacao_atual >= 100): // Bronze e acima 
                    ?>
                        <div class="certificado-card">
                            <strong>Certificado Bronze</strong>
                            <p>Por alcançar o nível Reciclador Bronze.</p>
                            <a href="../PHP/gerar_certificado.php?tipo=bronze" target="_blank">Baixar</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($pontuacao_atual >= 250): // Prata e acima 
                    ?>
                        <div class="certificado-card">
                            <strong>Certificado Prata</strong>
                            <p>Por alcançar o nível Reciclador Prata.</p>
                            <a href="../PHP/gerar_certificado.php?tipo=prata" target="_blank">Baixar</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($pontuacao_atual >= 500): // Apenas Ouro 
                    ?>
                        <div class="certificado-card">
                            <strong>Certificado Ouro</strong>
                            <p>Por alcançar o nível Reciclador Ouro.</p>
                            <a href="../PHP/gerar_certificado.php?tipo=ouro" target="_blank">Baixar</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="certificado-card"><strong>Embaixador Verde</strong>
                    <p>Por indicar 5 amigos.</p><a href="#">Compartilhar</a>
                </div>
            </div>


            <?php if ($perfil_proprio): ?>
                <div id="meu-dindin" class="tab-content">
                    <h3>Seu saldo DinDin Verde</h3>
                    <div class="saldo-box">
                        <div class="saldo-principal">
                            <strong><?php echo number_format($usuario_completo['saldo_ddv'], 2, ',', '.'); ?> <small>DDV</small></strong>
                            <a href="#" class="btn">Resgatar</a>
                        </div>
                        <hr>
                        <div class="saldo-detalhes">
                            <div class="saldo-item"><span>Disponível: </span><strong><?php echo number_format($usuario_completo['saldo_ddv'], 2, ',', '.'); ?> DDV</strong></div>
                            <div class="saldo-item"><span>Em processamento: </span><strong><?php echo number_format($usuario_completo['saldo_processamento'], 2, ',', '.'); ?> DDV</strong></div>
                            <div class="saldo-item"><span>Total acumulado: </span><strong><?php echo number_format($usuario_completo['saldo_total_acumulado'], 2, ',', '.'); ?> DDV</strong></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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