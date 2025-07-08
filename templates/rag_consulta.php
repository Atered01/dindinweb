<?php
// Em Dindinweb/templates/rag_consulta.php
require_once('../PHP/config.php');

// Verificação de acesso (admin ou empresa)
if ((!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) && !isset($_SESSION['empresa_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>RAG - Consulta Inteligente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/admin.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.5/purify.min.js"></script>
    <script>
        // Fallback seguro se o DOMPurify não carregar
        window.DOMPurify = window.DOMPurify || {
            sanitize: (html) => {
                console.warn('DOMPurify não carregado, usando fallback básico');
                return html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
            }
        };
    </script>
</head>

<body>
    <?php include '../includes/header_admin.php'; ?>

    <main class="admin-container">
        <h1>Consulta Inteligente (VerdIA)</h1>
        <p class="subtitle">Converse com o assistente para obter informações sobre a plataforma.</p>

        <div class="rag-container">
            <form id="rag-form">
                <div class="suggestion-area">
                    <span>Sugestões:</span>
                    <button class="suggestion-button">Quantos usuários temos?</button>
                    <button class="suggestion-button">Quais são as empresas parceiras?</button>
                    <button class="suggestion-button">Qual a recompensa mais cara?</button>
                    <button class="suggestion-button">Quais são as recompensas mais resgatadas?</button>
                </div>

                <input type="text" id="pergunta-input" placeholder="Faça uma pergunta..." required autocomplete="off">
                <button type="submit" class="btn btn-primary" title="Enviar"><i class="fas fa-paper-plane"></i></button>
            </form>

            <!-- Chat window único -->
            <div id="chat-window">
                <div class="chat-message assistant-message">
                    Olá! Eu sou o VerdIA, seu assistente administrativo. Como posso ajudar hoje?
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script id="rag-script-loader"
        src="<?php echo BASE_URL; ?>/js/rag_panel_handler.js"
        data-base-url="<?php echo BASE_URL; ?>"
        data-is-admin="<?php echo (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) ? '1' : '0'; ?>"
        defer>
    </script>
</body>
</html>
