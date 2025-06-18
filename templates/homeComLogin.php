<?php
// Inclui o config.php no topo para definir a BASE_URL
require_once('../PHP/config.php');

// =======================================================
// CORREÇÃO APLICADA AQUI:
// O "guarda" de segurança agora verifica o 'usuario_id' numérico.
// =======================================================
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

// Pega o nome completo da sessão e extrai o primeiro nome
$nomeCompleto = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiroNome = current(explode(' ', $nomeCompleto));
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/saiba-mais.css">
</head>

<body>

    <?php
    // Incluindo o cabeçalho reutilizável para usuários logados
    if (file_exists('../includes/header_logado.php')) {
        include '../includes/header_logado.php';
    }
    ?>

    <main>
        <section class="hero-section">
            <div class="container hero-container">
                <div class="hero-text">
                    <h1 class="hero-title">Bem-vindo(a) de volta, <?php echo htmlspecialchars($primeiroNome); ?>!</h1>
                    <p class="hero-subtitle">Acompanhe seus cashbacks, encontre pontos de coleta e continue ajudando o planeta com a DinDin Verde!</p>
                    <div class="hero-buttons">
                        <a href="<?php echo BASE_URL; ?>/templates/perfil.php" class="btn btn-light">Meu Perfil <i class="fas fa-arrow-right"></i></a>
                        <a href="<?php echo BASE_URL; ?>/templates/pontos_coleta.php" class="btn btn-outline-light">Ver Pontos de Coleta</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="../img/pessoas_reciclando.png" alt="Pessoas reciclando">
                </div>
            </div>
        </section>

        <?php
        if (file_exists('../includes/sobre_nos_section.php')) {
            include '../includes/sobre_nos_section.php';
        }
        ?>

        <?php
        // Incluindo a seção "Saiba Mais"
        if (file_exists('../includes/saiba_mais_section.php')) {
            include '../includes/saiba_mais_section.php';
        }
        ?>
    </main>

    <?php
    // Incluindo o rodapé reutilizável
    if (file_exists('../includes/footer.php')) {
        include '../includes/footer.php';
    }
    ?>

    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/contato.js"></script>
</body>

</html>