<?php
// Em Dindinweb/includes/header_admin.php

// Garante que a sessão foi iniciada e a BASE_URL está definida
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    require_once(dirname(__DIR__) . '/PHP/config.php');
}

// Extrai o primeiro nome para a saudação
$nomeCompleto = $_SESSION['usuario_nome'] ?? 'Admin';
$primeiroNome = current(explode(' ', $nomeCompleto));
?>
<header class="main-header admin-header">
    <nav class="container">
        <div class="navbar">
            <div class="nav-left">
                <a href="<?php echo BASE_URL; ?>/templates/admin_home.php" class="logo">
                    <i class="fas fa-shield-alt logo-icon"></i>
                    <span>Admin DinDin Verde</span>
                </a>
                <div class="nav-links">
                    <a href="<?php echo BASE_URL; ?>/templates/admin_home.php">Dashboard</a>
                </div>
            </div>
            <div class="nav-right">
                <div class="dropdown">
                    <button id="dropdown-toggle" class="dropdown-toggle">
                        <span>Olá, <?php echo htmlspecialchars($primeiroNome); ?>!</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="dropdown-menu" class="dropdown-menu">
                        <a href="<?php echo BASE_URL; ?>/PHP/view_as_user.php" class="dropdown-item" target="_blank"><i class="fas fa-globe"></i> Ver Site</a>
                        <a href="<?php echo BASE_URL; ?>/PHP/logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Sair</a>

                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>