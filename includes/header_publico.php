<?php
if (!defined('BASE_URL')) {
    require_once(dirname(__DIR__) . '/PHP/config.php');
}
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
                    <a href="<?php echo BASE_URL; ?>/PHP/index.php">Início</a>
                    <a href="<?php echo BASE_URL; ?>/templates/pontos_coleta.php">Pontos de Coleta</a>
                    <a href="<?php echo BASE_URL; ?>/templates/homeSemLogin.php#sobre-nos">Sobre Nós</a>
                    <a href="<?php echo BASE_URL; ?>/templates/homeSemLogin.php#how-it-works">Como Funciona</a>
                    <a href="<?php echo BASE_URL; ?>/templates/homeSemLogin.php#about">Saiba Mais</a>
                </div>
            </div>
            <div class="nav-right">
                <a href="<?php echo BASE_URL; ?>/templates/login.php" class="btn btn-secondary">Login</a>
                <a href="<?php echo BASE_URL; ?>/templates/cadastro.php" class="btn btn-primary">Cadastre-se</a>
            </div>
        </div>
    </nav>
</header>