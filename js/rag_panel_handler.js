// Em Dindinweb/js/rag_panel_handler.js
document.addEventListener("DOMContentLoaded", () => {
    // Fallbacks para bibliotecas, caso não carreguem
    const marked = window.marked || { parse: (text) => text.replace(/</g, "&lt;").replace(/>/g, "&gt;") };
    const DOMPurify = window.DOMPurify || { sanitize: (html) => html };

    // 1. MAPEAMENTO DOS ELEMENTOS DO HTML
    const ELEMENTS = {
        scriptTag: document.getElementById("rag-script-loader"),
        fabTrigger: document.getElementById("rag-fab-trigger"),
        overlay: document.getElementById("rag-overlay"),
        sidePanel: document.getElementById("rag-side-panel"),
        closeButton: document.getElementById("rag-panel-close"),
        ragForm: document.getElementById("rag-form"),
        perguntaInput: document.getElementById("pergunta-input"),
        chatWindow: document.getElementById("chat-window"),
        submitButton: document.querySelector('#rag-form button[type="submit"]'),
        suggestionButtons: document.querySelectorAll(".suggestion-button"),
    };

    if (!ELEMENTS.scriptTag || !ELEMENTS.fabTrigger || !ELEMENTS.ragForm) {
        console.error("Elementos essenciais para o RAG não encontrados.");
        return;
    }

    const BASE_URL = ELEMENTS.scriptTag.dataset.baseUrl;
    const IS_ADMIN = ELEMENTS.scriptTag.dataset.isAdmin === "1";
    const API_URL = `${BASE_URL}/PHP/rag_api.php`;

    // 2. FUNÇÕES DA INTERFACE
    const openPanel = () => {
        ELEMENTS.overlay?.classList.add("active");
        ELEMENTS.sidePanel?.classList.add("active");
        setTimeout(() => ELEMENTS.perguntaInput.focus(), 100);
    };

    const closePanel = () => {
        ELEMENTS.overlay?.classList.remove("active");
        ELEMENTS.sidePanel?.classList.remove("active");
    };

    const toggleSubmitButton = (disabled) => {
        if (!ELEMENTS.submitButton) return;
        ELEMENTS.submitButton.disabled = disabled;
        const iconClass = disabled ? "fas fa-spinner fa-spin" : "fas fa-paper-plane";
        ELEMENTS.submitButton.innerHTML = `<i class="${iconClass}"></i>`;
    };

    // 3. FUNÇÕES DE MANIPULAÇÃO DO CHAT
    const adicionarMensagem = (texto, tipo, { isLoading = false } = {}) => {
        const div = document.createElement("div");
        div.classList.add("chat-message", `${tipo}-message`);
        if (isLoading) {
            div.innerHTML = `<div class="message-loading"><span>${texto}</span></div>`;
        } else if (tipo === 'assistant') {
            div.innerHTML = DOMPurify.sanitize(marked.parse(texto));
        } else {
            div.textContent = texto;
        }
        ELEMENTS.chatWindow.appendChild(div);
        ELEMENTS.chatWindow.scrollTop = ELEMENTS.chatWindow.scrollHeight;
        return div;
    };

    const adicionarMensagemDebug = (obj) => {
        if (!IS_ADMIN) return;
        const texto = JSON.stringify(obj, null, 2);
        const div = document.createElement("div");
        div.classList.add("chat-message", "debug-message");
        div.innerHTML = `<details><summary>🔧 Detalhes Técnicos (Admin)</summary><pre><code>${texto}</code></pre></details>`;
        ELEMENTS.chatWindow.appendChild(div);
    };
    
    // 4. FUNÇÃO DE PARSE SEGURA (COM LIMPEZA DE MARKDOWN)
    async function safeJsonParse(text) {
        // **CORREÇÃO APLICADA AQUI**
        // Limpa o markdown ANTES de tentar o parse
        const cleanedText = text.replace(/^```json\s*|```\s*$/g, "").trim();
        try {
            return JSON.parse(cleanedText);
        } catch (e) {
            console.error("Falha ao fazer parse do JSON:", cleanedText);
            throw new Error("A resposta da IA não é um JSON válido.");
        }
    }

    // 5. FUNÇÃO CENTRAL DE CHAMADA À API
    async function chamarAPI(corpo) {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(corpo),
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.detalhes || `O servidor retornou um erro ${response.status}.`);
        }
        return response.json();
    }

    // 6. LÓGICA PRINCIPAL DO CHAT
    async function handleFormSubmit(event) {
        event.preventDefault();
        const perguntaUsuario = ELEMENTS.perguntaInput.value.trim();
        if (!perguntaUsuario) return;

        adicionarMensagem(perguntaUsuario, 'user');
        ELEMENTS.perguntaInput.value = "";
        toggleSubmitButton(true);

        const loadingMessage = adicionarMensagem("Analisando sua pergunta...", 'assistant', { isLoading: true });

        try {
            const promptFase1 = `Analise a pergunta: "${perguntaUsuario}". Se precisar de dados do banco, gere uma consulta SQL em formato JSON {"tipo_resposta": "sql", "conteudo": "SUA QUERY AQUI"}. Caso contrário, responda diretamente em formato JSON {"tipo_resposta": "direta", "conteudo": "SUA RESPOSTA AQUI"}.`;
            const respostaFase1 = await chamarAPI({ prompt: promptFase1 });
            
            // **CORREÇÃO APLICADA AQUI**
            // Usamos a nova função safeJsonParse
            const instrucaoIA = await safeJsonParse(respostaFase1.conteudo);
            adicionarMensagemDebug({ fase: 1, instrucao: instrucaoIA });

            if (instrucaoIA.tipo_resposta === "sql") {
                loadingMessage.querySelector('span').textContent = "Consultando banco de dados...";
                
                const dadosDoBanco = await chamarAPI({ query: instrucaoIA.conteudo, pergunta: perguntaUsuario });
                if (dadosDoBanco.erro) throw new Error(dadosDoBanco.erro);
                adicionarMensagemDebug({ fase: 2, dadosRecebidos: dadosDoBanco });

                loadingMessage.querySelector('span').textContent = "Gerando resposta final...";
                const promptFinal = `Com base nos seguintes dados: ${JSON.stringify(dadosDoBanco.dados)}. Responda de forma amigável à pergunta: "${perguntaUsuario}"`;
                const respostaFinal = await chamarAPI({ prompt: promptFinal });

                loadingMessage.innerHTML = DOMPurify.sanitize(marked.parse(respostaFinal.conteudo));

            } else {
                loadingMessage.innerHTML = DOMPurify.sanitize(marked.parse(instrucaoIA.conteudo));
            }

        } catch (erro) {
            const mensagemErro = `**Ocorreu um erro:**\n\n${erro.message}`;
            loadingMessage.innerHTML = DOMPurify.sanitize(marked.parse(mensagemErro));
            adicionarMensagemDebug({ erro, stack: erro.stack });
        } finally {
            toggleSubmitButton(false);
        }
    }

    // 7. INICIALIZAÇÃO DOS EVENTOS
    ELEMENTS.fabTrigger.addEventListener("click", (e) => { e.preventDefault(); openPanel(); });
    ELEMENTS.closeButton?.addEventListener("click", closePanel);
    ELEMENTS.overlay?.addEventListener("click", closePanel);
    ELEMENTS.ragForm.addEventListener("submit", handleFormSubmit);
    ELEMENTS.suggestionButtons.forEach(button => {
        button.addEventListener("click", function() {
            ELEMENTS.perguntaInput.value = this.textContent;
            ELEMENTS.ragForm.dispatchEvent(new Event("submit", { bubbles: true }));
        });
    });
});