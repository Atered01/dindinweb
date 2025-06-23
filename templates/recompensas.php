<?php
require_once('../PHP/config.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$mensagem_sucesso = $_SESSION['sucesso_resgate'] ?? null;
$mensagem_erro = $_SESSION['erro_resgate'] ?? null;
unset($_SESSION['sucesso_resgate'], $_SESSION['erro_resgate']);

try {
    // Busca o saldo do usuário e todas as recompensas ativas
    $stmt_saldo = $pdo->prepare("SELECT saldo_ddv FROM estatisticas_usuario WHERE usuario_id_personalizado = ?");
    $stmt_saldo->execute([$id_usuario]);
    $saldo_usuario = $stmt_saldo->fetchColumn();

    $stmt_recompensas = $pdo->query("SELECT id, nome, custo_em_ddv, imagem_url, slug FROM recompensas WHERE ativo = TRUE ORDER BY tipo, custo_em_ddv");
    $recompensas = $stmt_recompensas->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar recompensas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recompensas - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/perfil.css">
</head>

<body>
    <?php include '../includes/header_logado.php'; ?>

    <main class="container" style="padding-top: 2rem;">
        <h1>Vitrine de Recompensas</h1>
        <p class="subtitle">Use seus créditos DinDin Verde para resgatar vouchers incríveis!</p>

        <div class="saldo-box" style="margin-bottom: 2rem;">
            <div class="saldo-principal">
                <span>Seu Saldo:</span>
                <strong><?php echo number_format($saldo_usuario, 2, ',', '.'); ?> <small>DDV</small></strong>
            </div>
        </div>

        <?php if ($mensagem_sucesso): ?>
            <div class="success-message" style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #66bb6a; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo htmlspecialchars($mensagem_sucesso); ?>
            </div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="error-message" style="border-color: #ef4444; background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                <?php echo htmlspecialchars($mensagem_erro); ?>
            </div>
        <?php endif; ?>

        <div class="grid-3">
            <?php foreach ($recompensas as $recompensa): ?>
                <a href="recompensa_detalhe.php?slug=<?php echo $recompensa['slug']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="certificado-card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='var(--shadow-lg)';" onmouseout="this.style.transform='none'; this.style.boxShadow='var(--shadow-md)';">
                        <img src="<?php echo htmlspecialchars($recompensa['imagem_url']); ?>" alt="<?php echo htmlspecialchars($recompensa['nome']); ?>" style="width: 100%; height: 150px; object-fit: cover; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <div>
                            <strong><?php echo htmlspecialchars($recompensa['nome']); ?></strong>
                            <p>Custo: <strong style="color: var(--color-primary-dark);"><?php echo number_format($recompensa['custo_em_ddv'], 2, ',', '.'); ?> DDV</strong></p>
                        </div>
                        <span class="btn" style="margin-top: 1rem; pointer-events: none; background-color: var(--color-primary-light); border: none;">Ver Detalhes</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>

</html>