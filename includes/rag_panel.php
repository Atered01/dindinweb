<a href="#" id="rag-fab-trigger" class="rag-fab" title="Consulta Inteligente"><i class="fas fa-robot"></i></a>
<div class="rag-overlay" id="rag-overlay"></div>

<div class="rag-side-panel" id="rag-side-panel">
    <div class="rag-panel-header">
        <h3>Consulta Inteligente (VerdIA)</h3>
        <button class="rag-panel-close" id="rag-panel-close" title="Fechar">&times;</button>
    </div>

    <div id="chat-window">
        <div class="chat-message assistant-message">
            Olá! Eu sou o VerdIA, seu assistente administrativo. Como posso ajudar hoje?
        </div>
    </div>

    <div class="suggestion-area">
        <span>Sugestões:</span>
        <button class="suggestion-button">Quantos usuários temos?</button>
        <button class="suggestion-button">Quais são as empresas parceiras?</button>
        <button class="suggestion-button">Qual a recompensa mais cara?</button>
    </div>

    <form id="rag-form">
        <input type="text" id="pergunta-input" placeholder="Faça uma pergunta..." required autocomplete="off">
        <button type="submit" class="btn btn-primary" title="Enviar"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<script id="rag-script-loader" src="<?php echo BASE_URL; ?>/js/rag_panel_handler.js"
    data-base-url="<?php echo BASE_URL; ?>"
    data-api-key="<?php echo htmlspecialchars($gemini_api_key ?? ''); ?>"
    data-is-admin="<?php echo (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) ? '1' : '0'; ?>"
    defer>
</script>