<?php
// Em Dindinweb/includes/header_empresa.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    require_once(dirname(__DIR__) . '/PHP/config.php');
}
$nomeEmpresa = $_SESSION['empresa_nome'] ?? 'Parceiro';
?>
<header class="main-header" style="background-color: #1e293b;">
    <nav class="container">
        <div class="navbar">
            <div class="nav-left">
                <a href="<?php echo BASE_URL; ?>/templates/painel_b2b.php" class="logo">
                    <i class="fas fa-building logo-icon" style="color: #fff;"></i>
                    <span style="color: #fff;"><?php echo htmlspecialchars($nomeEmpresa); ?></span>
                </a>
            </div>
            <div class="nav-right">
                <div class="dropdown">
                    <button id="dropdown-toggle" class="dropdown-toggle" style="color: #fff;">
                        <span>Menu</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="dropdown-menu" class="dropdown-menu">
                        <a href="<?php echo BASE_URL; ?>/PHP/logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Sair</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
