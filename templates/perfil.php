<?php
require_once('../PHP/config.php');
require_once('../PHP/helpers.php');

// Protege o acesso
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

// Decide se mostra o próprio perfil ou de outro usuário (caso admin)
$id_usuario_para_exibir = $_SESSION['usuario_id'];
if (isset($_GET['id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    $id_usuario_para_exibir = filter_var($_GET['id'], FILTER_VALIDATE_INT);
}

// Busca os dados do perfil
try {
    $sql = "SELECT u.id, u.nome, u.id_personalizado, u.data_cadastro, u.foto_perfil, 
                   e.itens_reciclados, e.mercados_visitados, e.meses_consecutivos,
                   e.co2_evitado, e.agua_economizada, e.energia_poupada,
                   e.saldo_ddv, e.saldo_processamento, e.saldo_total_acumulado
            FROM usuarios u
            LEFT JOIN estatisticas_usuario e ON u.id = e.id_usuario
            WHERE u.id = :id_usuario";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_usuario' => $id_usuario_para_exibir]);
    $usuario_completo = $stmt->fetch();

    if (!$usuario_completo) {
        die("Usuário não encontrado.");
    }

    // Preenche valores padrões se ausentes
    $usuario_completo = array_merge([
        'itens_reciclados' => 0, 'mercados_visitados' => 0, 'meses_consecutivos' => 0,
        'co2_evitado' => 0, 'agua_economizada' => 0, 'energia_poupada' => 0,
        'saldo_ddv' => 0, 'saldo_processamento' => 0, 'saldo_total_acumulado' => 0
    ], $usuario_completo);

    // Calcula meses como membro
    $dataCadastro = new DateTime($usuario_completo['data_cadastro']);
    $dataAtual = new DateTime();
    $diferenca = $dataCadastro->diff($dataAtual);
    $mesesComoMembro = ($diferenca->y * 12) + $diferenca->m;
    if ($diferenca->days > 0 && $mesesComoMembro == 0) $mesesComoMembro = 1;

    // Pontuação e nível
    $pontuacao_atual = $usuario_completo['co2_evitado'] + $usuario_completo['agua_economizada'] + $usuario_completo['energia_poupada'];
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
    <title>Perfil - <?php echo htmlspecialchars($usuario_completo['nome']); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include '../includes/header_logado.php'; ?>

<div class="perfil-container">
    <aside class="perfil-lateral">
        <div class="perfil-header">
            <div class="perfil-avatar-upload">
                <img src="../PHP/exibir_foto.php<?php echo ($id_usuario_para_exibir != $_SESSION['usuario_id']) ? '?id=' . $id_usuario_para_exibir : ''; ?>" alt="Foto de Perfil" class="perfil-foto">
            </div>
            <h2><?php echo htmlspecialchars($usuario_completo['nome']); ?></h2>
            <p>Membro desde <?php echo date('M/Y', strtotime($usuario_completo['data_cadastro'])); ?></p>
        </div>

        <?php if ($id_usuario_para_exibir == $_SESSION['usuario_id']): ?>
        <div class="upload-form">
            <form action="../PHP/upload_foto.php" method="post" enctype="multipart/form-data">
                <label for="foto" class="btn-upload">Trocar Foto</label>
                <input type="file" name="foto_perfil" id="foto" required style="display:none;" onchange="this.form.submit()">
            </form>
        </div>
        <?php endif; ?>

        <div class="perfil-stats">
            <div><strong><?php echo $usuario_completo['itens_reciclados']; ?></strong><span>Itens</span></div>
            <div><strong><?php echo $usuario_completo['mercados_visitados']; ?></strong><span>Mercados</span></div>
            <div><strong><?php echo $mesesComoMembro; ?></strong><span>Meses</span></div>
        </div>

        <div class="perfil-nivel">
            <p>Nível: <strong><?php echo $nivel_usuario; ?></strong></p>
            <div class="progress-bar">
                <div style="width: <?php echo $progresso_percentual; ?>%;"></div>
            </div>
            <small><?php echo htmlspecialchars($proximoNivelTexto); ?></small>
        </div>
    </aside>

    <main class="conteudo-principal">
        <div class="tabs">
            <button class="tab-button active" data-target="#impacto">Meu Impacto</button>
            <button class="tab-button" data-target="#dindin">Meu DinDin</button>
        </div>

        <div id="impacto" class="tab-content active">
            <h3>Impacto ambiental positivo</h3>
            <div class="grid-3">
                <div class="stat-box"><strong><?php echo number_format($usuario_completo['co2_evitado'], 1, ',', '.'); ?> kg</strong><span>CO2 evitado</span></div>
                <div class="stat-box"><strong><?php echo number_format($usuario_completo['agua_economizada'], 0, ',', '.'); ?> L</strong><span>Água economizada</span></div>
                <div class="stat-box"><strong><?php echo number_format($usuario_completo['energia_poupada'], 0, ',', '.'); ?> kWh</strong><span>Energia poupada</span></div>
            </div>
        </div>

        <div id="dindin" class="tab-content">
            <h3>Seu saldo DinDin Verde</h3>
            <div class="saldo-box">
                <div class="saldo-pr
