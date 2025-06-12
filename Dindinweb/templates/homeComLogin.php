<?php
session_start();

// CORREÇÃO: Incluindo o config.php no topo para definir a BASE_URL
require_once('../PHP/config.php');

// O "guarda" de segurança que você já tinha
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nome'])) {
    header('Location: login.php');
    exit();
}

// Lógica para capturar a mensagem de sucesso de login
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = $_SESSION['mensagem_sucesso'];
    unset($_SESSION['mensagem_sucesso']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>../css/saiba-mais.css">
</head>
<body>
    
    <?php include '../includes/header_logado.php'; ?>

    <main>
        <div class="container">
            <?php if (isset($mensagem_sucesso)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($mensagem_sucesso); ?>
                </div>
            <?php endif; ?>
        </div>

       <section class="hero-section">
            <div class="container hero-container">
                <div class="hero-text">
                    <h1 class="hero-title">Bem-vindo(a) de volta, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h1>
                    <p class="hero-subtitle">Acompanhe seus cashbacks, encontre pontos de coleta e continue ajudando o planeta com a DinDin Verde!</p>
                    <div class="hero-buttons">
                        <a href="<?php echo BASE_URL; ?>/templates/perfil.php" class="btn btn-light">Meu Perfil <i class="fas fa-arrow-right"></i></a>
                        <a href="<?php echo BASE_URL; ?>/templates/pontos_coleta.php" class="btn btn-outline-light">Ver Pontos de Coleta</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1606787366850-de6330128bfc?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Pessoas reciclando">
                </div>
            </div>
        </section>
       
       <?php include '../includes/saiba_mais_section.php'; ?>
    </main>
    
    <?php include '../includes/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>