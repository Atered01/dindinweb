<?php
// Em Dindinweb/templates/gerenciar_recompensas.php
require_once('../PHP/config.php');

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$mensagem_sucesso = $_SESSION['sucesso_recompensa'] ?? null;
$mensagem_erro = $_SESSION['erro_recompensa'] ?? null;
unset($_SESSION['sucesso_recompensa'], $_SESSION['erro_recompensa']);

try {
    $recompensas = $pdo->query("SELECT * FROM recompensas ORDER BY nome ASC")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar recompensas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Recompensas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/cadastro.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
</head>

<body>
    <?php include '../includes/header_admin.php'; ?>

    <main class="admin-container">
        <h1>Gerenciar Recompensas</h1>
        <p class="subtitle">Adicione novas recompensas e gerencie as existentes.</p>

        <div class="user-management">
            <h2>Adicionar Nova Recompensa</h2>

            <?php if ($mensagem_sucesso): ?>
                <div class="success-message" style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #66bb6a; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                    <?php echo htmlspecialchars($mensagem_sucesso); ?>
                </div>
            <?php endif; ?>
            <?php if ($mensagem_erro): ?>
                <div class="error-message" style="padding: 1rem; margin-bottom: 1.5rem; text-align: center;">
                    <?php echo htmlspecialchars($mensagem_erro); ?>
                </div>
            <?php endif; ?>

            <form action="../PHP/adicionar_recompensa.php" method="POST" enctype="multipart/form-data" id="cadastro-container" style="margin: 0; padding: 1.5rem;">
                <div class="form-group">
                    <label for="nome">Nome da Recompensa</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 8px;"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="custo_em_ddv">Custo em DDV</label>
                        <input type="number" id="custo_em_ddv" name="custo_em_ddv" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px;">
                            <option value="parque">Parque</option>
                            <option value="zoo">Zoológico</option>
                            <option value="aquario">Aquário</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="imagem">Imagem da Recompensa</label>
                    <input type="file" id="imagem" name="imagem" accept="image/jpeg, image/png" required>
                </div>
                <div class="button-group">
                    <button type="submit" class="register-button">Adicionar Recompensa</button>
                </div>
            </form>
        </div>

        <hr style="margin: 2rem 0;">

        <div class="user-management">
            <h2>Recompensas Atuais</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Custo (DDV)</th>
                        <th>Tipo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recompensas as $r): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($r['imagem_url']); ?>" alt="Imagem" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                            <td><?php echo htmlspecialchars($r['nome']); ?></td>
                            <td><?php echo number_format($r['custo_em_ddv'], 2, ',', '.'); ?></td>
                            <td><?php echo ucfirst($r['tipo']); ?></td>
                            <td><?php echo $r['ativo'] ? 'Ativo' : 'Inativo'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>

</html>