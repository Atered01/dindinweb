<?php
require_once('../PHP/config.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

$id_recompensa = $_GET['id'] ?? null;
if (!$id_recompensa) {
    // CORREÇÃO: Usando slug como identificador, conforme implementamos
    $slug_recompensa = $_GET['slug'] ?? null;
    if (!$slug_recompensa) {
        die("Recompensa não especificada.");
    }
    $id_recompensa = $slug_recompensa; // A variável agora contém o slug
    $coluna_busca = 'slug'; // Vamos buscar pelo slug
} else {
    $coluna_busca = 'id'; // Mantém compatibilidade se o ID ainda for usado
}


try {
    // Busca os detalhes da recompensa principal (agora pode buscar por ID ou SLUG)
    $stmt = $pdo->prepare("SELECT * FROM recompensas WHERE $coluna_busca = ? AND ativo = TRUE");
    $stmt->execute([$id_recompensa]);
    $recompensa = $stmt->fetch();

    if (!$recompensa) {
        die("Recompensa não encontrada ou inativa.");
    }

    // Busca as imagens da galeria para esta recompensa
    $stmt_imagens = $pdo->prepare("SELECT url_imagem FROM recompensa_imagens WHERE recompensa_id = ? ORDER BY ordem");
    $stmt_imagens->execute([$recompensa['id']]); // Usamos o ID numérico aqui
    $imagens_galeria = $stmt_imagens->fetchAll(PDO::FETCH_COLUMN);

    // Busca o saldo do usuário
    $stmt_saldo = $pdo->prepare("SELECT saldo_ddv FROM estatisticas_usuario WHERE usuario_id_personalizado = ?");
    $stmt_saldo->execute([$_SESSION['usuario_id']]);
    $saldo_usuario = $stmt_saldo->fetchColumn();

} catch (PDOException $e) {
    die("Erro ao carregar detalhes da recompensa: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recompensa['nome']); ?> - DinDin Verde</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/perfil.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">

    <style>
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1rem; }
        .gallery-grid img { width: 100%; height: 120px; object-fit: cover; border-radius: 0.5rem; }
    </style>
</head>
<body>
    <?php include '../includes/header_logado.php'; ?>

    <main class="container" style="padding-top: 2rem;">
        <a href="recompensas.php" style="text-decoration: none; color: var(--color-primary-dark); font-weight: 600;">&larr; Voltar para a vitrine</a>
        
        <div style="display: flex; gap: 2rem; margin-top: 1rem; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <img src="<?php echo htmlspecialchars($recompensa['imagem_url']); ?>" alt="<?php echo htmlspecialchars($recompensa['nome']); ?>" style="width: 100%; height: auto; border-radius: 0.5rem;">
                <?php if (!empty($imagens_galeria)): ?>
                    <h3 style="margin-top: 1.5rem;">Galeria</h3>
                    <div class="gallery-grid">
                        <?php foreach ($imagens_galeria as $img_url): ?>
                            <img src="<?php echo htmlspecialchars($img_url); ?>" alt="Galeria de <?php echo htmlspecialchars($recompensa['nome']); ?>">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div style="flex: 1; min-width: 300px;">
                <h1><?php echo htmlspecialchars($recompensa['nome']); ?></h1>
                <p class="subtitle" style="text-align: left; font-size: 1rem;"><?php echo nl2br(htmlspecialchars($recompensa['descricao'])); ?></p>
                <hr style="margin: 1.5rem 0;">
                <div class="saldo-box" style="padding: 1rem;">
                    <p style="font-size: 1.25rem;">Custo: <strong style="color: var(--color-primary-dark);"><?php echo number_format($recompensa['custo_em_ddv'], 2, ',', '.'); ?> DDV</strong></p>
                    <p style="font-size: 0.9rem;">Seu Saldo: <?php echo number_format($saldo_usuario, 2, ',', '.'); ?> DDV</p>
                </div>
                
                <div style="margin-top: 2rem;">
                    <?php if ($saldo_usuario >= $recompensa['custo_em_ddv']): ?>
                        <a href="../PHP/resgatar_recompensa.php?id=<?php echo $recompensa['id']; ?>" class="btn btn-full" style="padding: 0.8rem;" onclick="return confirm('Tem certeza que deseja resgatar este voucher por <?php echo $recompensa['custo_em_ddv']; ?> DDV?');">Resgatar Agora</a>
                    <?php else: ?>
                        <button class="btn btn-full" disabled style="background-color: #ccc; border-color: #ccc; cursor: not-allowed; padding: 0.8rem;">Saldo Insuficiente</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>