<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Pega o nome completo da sessão e extrai o primeiro nome
$nomeCompleto = $_SESSION['usuario_nome'] ?? 'Usuário';
$primeiroNome = current(explode(' ', $nomeCompleto));
?>
<header class="main-header">
    <nav class="container">
        <div class="navbar">
            <div class="nav-left">
                <a href="<?php echo BASE_URL; ?>/PHP/index.php" class="logo">
                    <i class="fas fa-leaf logo-icon"></i>
                    <span>DinDin Verde</span>
                </a>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/templates/homeComLogin.php">Início</a>
                    <a href="<?php echo BASE_URL; ?>/templates/pontos_coleta.php">Pontos de Coleta</a>
                    <a href="<?php echo BASE_URL; ?>/templates/homeComLogin.php#about">Saiba Mais</a>
                </div>
            </div>
            <div class="nav-right">
                <div class="dropdown">
                    <button id="dropdown-toggle" class="dropdown-toggle">
                        <span>Olá, <?php echo htmlspecialchars($primeiroNome); ?>!</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="dropdown-menu" class="dropdown-menu">
                        <a href="<?php echo BASE_URL; ?>/templates/perfil.php" class="dropdown-item"><i class="fas fa-user"></i> Meu Perfil</a>
                        <a href="<?php echo BASE_URL; ?>/templates/configuracoes.php" class="dropdown-item"><i class="fas fa-cog"></i> Configurações</a>
                        <a href="<?php echo BASE_URL; ?>/PHP/logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Sair</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>