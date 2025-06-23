<?php
require_once('../PHP/config.php');
require_once('../PHP/helpers.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

$id_usuario_para_exibir = $_SESSION['usuario_id'];
$perfil_proprio = true;

if (isset($_GET['id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    $id_selecionado = preg_match('/^[A-Za-z0-9_-]+$/', $_GET['id']) ? $_GET['id'] : null;
    if ($id_selecionado) {
        $id_usuario_para_exibir = $id_selecionado;
        $perfil_proprio = ($id_usuario_para_exibir === $_SESSION['usuario_id']);
    }
}

try {
    $sql = "SELECT u.*, 
                   e.co2_evitado, e.agua_economizada, e.energia_poupada, 
                   e.itens_reciclados, e.mercados_visitados, e.meses_consecutivos, 
                   e.nivel, e.progresso_nivel, e.saldo_ddv, 
                   e.saldo_processamento, e.saldo_total_acumulado
            FROM usuarios u
            LEFT JOIN estatisticas_usuario e ON u.id_personalizado = e.usuario_id_personalizado
            WHERE u.id_personalizado = :id_usuario";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_usuario' => $id_usuario_para_exibir]);
    $usuario_completo = $stmt->fetch();

    if (!$usuario_completo) {
        die("Usuário não encontrado.");
    }

    // Continuação igual...


    // Garante que todos os campos de estatísticas existam para evitar erros
    if ($usuario_completo['itens_reciclados'] === null) {
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

    // Calcula o total de meses como membro dinamicamente
    $dataCadastro = new DateTime($usuario_completo['data_cadastro']);
    $dataAtual = new DateTime('now');
    $diferenca = $dataCadastro->diff($dataAtual);
    $mesesComoMembro = ($diferenca->y * 12) + $diferenca->m;
    if ($diferenca->days >= 0 && $mesesComoMembro == 0) {
        $mesesComoMembro = 1;
    }

    // A pontuação agora é calculada com base nas estatísticas
    $pontuacao_atual = ($usuario_completo['co2_evitado'] ?? 0) + ($usuario_completo['agua_economizada'] ?? 0) + ($usuario_completo['energia_poupada'] ?? 0);
    $proximoNivelTexto = '';
    // Usa as funções do helpers.php
    $nivel_usuario = determinarNivel($pontuacao_atual);
    $progresso_percentual = (float) calcularProgresso($pontuacao_atual, $proximoNivelTexto);

    $sql_vouchers = "SELECT rr.*, r.nome, r.descricao 
                 FROM recompensas_resgatadas rr
                 JOIN recompensas r ON rr.recompensa_id = r.id
                 WHERE rr.usuario_id_personalizado = :id_usuario
                 ORDER BY rr.data_resgate DESC";
    $stmt_vouchers = $pdo->prepare($sql_vouchers);
    $stmt_vouchers->execute([':id_usuario' => $id_usuario_para_exibir]);
    $vouchers = $stmt_vouchers->fetchAll();
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
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
            <div class="perfil-recompensas">
                <h3>Últimos Vouchers Resgatados</h3>
                <?php if (!$perfil_proprio): ?>
                    <p style="font-size: 0.8rem; color: #6b7280;">Os vouchers são visíveis apenas pelo dono do perfil.</p>
                <?php elseif (empty($vouchers)): ?>
                    <p style="font-size: 0.8rem; color: #6b7280;">Nenhum voucher resgatado ainda.</p>
                <?php else: ?>
                    <?php
                    // Pega apenas os 2 últimos vouchers para exibir no painel
                    $ultimos_vouchers = array_slice($vouchers, 0, 2);
                    foreach ($ultimos_vouchers as $voucher):
                        // Define o ícone com base no tipo da recompensa (você precisa ter a coluna 'tipo' na sua query)
                        $icone = 'fa-ticket-alt'; // Ícone padrão
                        if (isset($voucher['tipo'])) {
                            switch ($voucher['tipo']) {
                                case 'parque':
                                    $icone = 'fa-tree';
                                    break;
                                case 'zoo':
                                    $icone = 'fa-paw';
                                    break;
                                case 'aquario':
                                    $icone = 'fa-fish';
                                    break;
                            }
                        }
                    ?>
                        <div class="recompensa-item">
                            <i class="fas <?php echo $icone; ?>"></i>
                            <span><strong><?php echo htmlspecialchars(substr($voucher['nome'], 0, 25)) . '...'; ?></strong></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php if ($perfil_proprio): ?>
                <a href="recompensas.php" class="btn">Ver Vitrine de Recompensas</a>
            <?php endif; ?>
            <a href="recompensas.php" class="btn">Ver todas as recompensas</a>
        </aside>

        <main class="conteudo-principal">
            <div class="tabs">
                <button class="tab-button active" data-target="#meu-impacto">Impacto</button>
                <?php if ($perfil_proprio): ?>
                    <button class="tab-button" data-target="#meu-dindin">Meu DinDin</button>
                    <button class="tab-button" data-target="#meus-vouchers">Meus Vouchers</button>
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
                    <?php if ($pontuacao_atual >= 5000): ?>
                        <div class="certificado-card"><strong>Certificado Bronze</strong>
                            <p>Por um grande impacto ambiental.</p><a href="../PHP/gerar_certificado.php?tipo=bronze" target="_blank">Baixar</a>
                        </div>
                    <?php endif; ?>
                    <?php if ($pontuacao_atual >= 15000): ?>
                        <div class="certificado-card"><strong>Certificado Prata</strong>
                            <p>Por um excelente impacto ambiental.</p><a href="../PHP/gerar_certificado.php?tipo=prata" target="_blank">Baixar</a>
                        </div>
                    <?php endif; ?>
                    <?php if ($pontuacao_atual >= 30000): ?>
                        <div class="certificado-card"><strong>Certificado Ouro</strong>
                            <p>Por um impacto ambiental exemplar.</p><a href="../PHP/gerar_certificado.php?tipo=ouro" target="_blank">Baixar</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($perfil_proprio): ?>
                <div id="meu-dindin" class="tab-content">
                    <h3>Seu saldo DinDin Verde</h3>
                    <div class="saldo-box">
                        <div class="saldo-principal">
                            <strong><?php echo number_format($usuario_completo['saldo_ddv'], 2, ',', '.'); ?> <small>DDV</small></strong>
                            <a href="recompensas.php" class="btn">Resgatar</a>
                        </div>
                        <hr>
                        <div class="saldo-detalhes">
                            <div class="saldo-item"><span>Disponível</span><strong><?php echo number_format($usuario_completo['saldo_ddv'], 2, ',', '.'); ?> DDV</strong></div>
                            <div class="saldo-item"><span>Em processamento</span><strong><?php echo number_format($usuario_completo['saldo_processamento'], 2, ',', '.'); ?> DDV</strong></div>
                            <div class="saldo-item"><span>Total acumulado</span><strong><?php echo number_format($usuario_completo['saldo_total_acumulado'], 2, ',', '.'); ?> DDV</strong></div>
                        </div>
                    </div>
                </div>

                <div id="meus-vouchers" class="tab-content">
                    <h3>Seus Vouchers Resgatados</h3>
                    <?php if (count($vouchers) > 0): ?>
                        <div class="grid-2">
                            <?php foreach ($vouchers as $voucher): ?>
                                <div class="certificado-card">
                                    <strong><?php echo htmlspecialchars($voucher['nome']); ?></strong>
                                    <p><?php echo htmlspecialchars($voucher['descricao']); ?></p>
                                    <hr style="margin: 0.5rem 0;">
                                    <p><strong>Código:</strong> <?php echo htmlspecialchars($voucher['codigo_voucher']); ?></p>
                                    <p><strong>Expira em:</strong> <?php echo date('d/m/Y', strtotime($voucher['data_expiracao'])); ?></p>
                                    <?php
                                    $hoje = new DateTime();
                                    $data_exp = new DateTime($voucher['data_expiracao']);
                                    if ($voucher['utilizado']) {
                                        echo '<p style="color: grey;">Status: Utilizado</p>';
                                    } elseif ($hoje > $data_exp) {
                                        echo '<p style="color: red;">Status: Expirado</p>';
                                    } else {
                                        echo '<p style="color: green;">Status: Válido</p>';
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Você ainda não resgatou nenhuma recompensa. <a href="recompensas.php">Clique aqui</a> para ver a vitrine!</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        const DADOS_PERFIL = {
            pontuacao: <?php echo $pontuacao_atual; ?>,
            co2: <?php echo $usuario_completo['co2_evitado'] ?? 0; ?>,
            agua: <?php echo $usuario_completo['agua_economizada'] ?? 0; ?>,
            energia: <?php echo $usuario_completo['energia_poupada'] ?? 0; ?>
        };
    </script>

    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/perfil.js"></script>
</body>

</html>