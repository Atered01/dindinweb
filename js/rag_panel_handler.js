document.addEventListener("DOMContentLoaded", () => {
    // Funções para sanitizar e renderizar HTML (marked e DOMPurify)
    const marked = window.marked || { parse: (text) => String(text || '').replace(/</g, "&lt;").replace(/>/g, "&gt;") };
    const DOMPurify = window.DOMPurify || { sanitize: (html) => html };

    // Mapeamento dos elementos da interface para fácil acesso
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

    if (!ELEMENTS.scriptTag) return;

    // Constantes de configuração
    const API_URL = "http://localhost:5000/rag";
    const IS_ADMIN = ELEMENTS.scriptTag.dataset.isAdmin === "1";

    // --- Funções Auxiliares da Interface ---

    /** Abre o painel de chat. */
    const openPanel = () => {
        ELEMENTS.overlay?.classList.add("active");
        ELEMENTS.sidePanel?.classList.add("active");
        setTimeout(() => ELEMENTS.perguntaInput.focus(), 100); // Foca no input após a animação
    };

    /** Fecha o painel de chat. */
    const closePanel = () => {
        ELEMENTS.overlay?.classList.remove("active");
        ELEMENTS.sidePanel?.classList.remove("active");
    };

    /** Ativa/desativa o botão de envio e mostra um ícone de carregamento. */
    const toggleSubmitButton = (disabled) => {
        if (!ELEMENTS.submitButton) return;
        ELEMENTS.submitButton.disabled = disabled;
        ELEMENTS.submitButton.innerHTML = `<i class="fas ${disabled ? 'fa-spinner fa-spin' : 'fa-paper-plane'}"></i>`;
    };

    /**
     * Adiciona uma nova mensagem à janela de chat.
     * @param {string} texto - O conteúdo da mensagem.
     * @param {'user' | 'assistant'} tipo - O tipo de mensagem (usuário ou assistente).
     * @param {object} options - Opções adicionais, como { isLoading: true }.
     * @returns {HTMLElement} O elemento da mensagem criada.
     */
    const adicionarMensagem = (texto, tipo, { isLoading = false } = {}) => {
        const div = document.createElement("div");
        div.classList.add("chat-message", `${tipo}-message`);

        if (isLoading) {
            // Mensagem de carregamento
            div.innerHTML = `<div class="message-loading"><span>${texto}</span></div>`;
        } else if (tipo === 'assistant') {
            // Mensagem do assistente (renderiza Markdown)
            div.innerHTML = DOMPurify.sanitize(marked.parse(texto));
        } else {
            // Mensagem do usuário (texto simples)
            div.textContent = texto;
        }

        ELEMENTS.chatWindow.appendChild(div);
        ELEMENTS.chatWindow.scrollTop = ELEMENTS.chatWindow.scrollHeight; // Rola para o final
        return div;
    };

    /** Adiciona uma mensagem de debug visível apenas para administradores. */
    const adicionarMensagemDebug = (obj) => {
        if (!IS_ADMIN) return;
        const texto = JSON.stringify(obj, null, 2);
        const div = document.createElement("div");
        div.classList.add("chat-message", "debug-message");
        div.innerHTML = `<details><summary>🔧 Detalhes Técnicos (Admin)</summary><pre><code>${texto}</code></pre></details>`;
        ELEMENTS.chatWindow.appendChild(div);
    };

    // --- Lógica Principal ---

    /**
     * Lida com o envio do formulário de pergunta.
     * @param {Event} event - O evento de submissão do formulário.
     */
    async function handleFormSubmit(event) {
        event.preventDefault(); // Impede o recarregamento da página
        const perguntaUsuario = ELEMENTS.perguntaInput.value.trim();
        if (!perguntaUsuario) return;

        // Adiciona a mensagem do usuário à interface e limpa o input
        adicionarMensagem(perguntaUsuario, 'user');
        ELEMENTS.perguntaInput.value = "";
        toggleSubmitButton(true);
        const loadingMessage = adicionarMensagem("Pensando...", 'assistant', { isLoading: true });

        try {
            // Envia a pergunta para a API
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: perguntaUsuario })
            });

            const resultado = await response.json();

            // Lança um erro se a resposta da API não for bem-sucedida
            if (!response.ok) {
                throw new Error(resultado?.erro || `Erro de comunicação com o servidor: ${response.status}`);
            }

            // *** INÍCIO DA INTEGRAÇÃO ***
            // Verifica se a resposta contém a chave 'dados'
            if (resultado.dados) {
                // Caso 1: A resposta é um conjunto de dados (ex: relatório)
                // Formata os dados como um bloco de código JSON para exibição
                const dadosFormatados = "```json\n" + JSON.stringify(resultado.dados, null, 2) + "\n```";
                const mensagemFinal = "Consulta realizada com sucesso! Aqui estão os dados solicitados:\n\n" + dadosFormatados;
                loadingMessage.innerHTML = DOMPurify.sanitize(marked.parse(mensagemFinal));

            } else {
                // Caso 2: A resposta é textual da IA
                let mensagemFinal = resultado.resposta || "**Erro:** A resposta do assistente está vazia.";

                // Adiciona informações extras, se existirem
                if (resultado.informacao) {
                    mensagemFinal += `\n\n*Informações adicionais: ${resultado.informacao}*`;
                }
                
                loadingMessage.innerHTML = DOMPurify.sanitize(marked.parse(mensagemFinal));
            }
            // *** FIM DA INTEGRAÇÃO ***

            // Exibe informações de debug se existirem e o usuário for admin
            if (resultado.debug_info) {
                adicionarMensagemDebug(resultado.debug_info);
            }

        } catch (erro) {
            // Em caso de erro, exibe uma mensagem de erro na interface
            loadingMessage.innerHTML = DOMPurify.sanitize(marked.parse(`**Ocorreu um erro:**\n\n${erro.message}`));
            if (IS_ADMIN) {
                adicionarMensagemDebug({ erro: erro.message, stack: erro.stack });
            }
        } finally {
            // Reativa o botão de envio, independentemente do resultado
            toggleSubmitButton(false);
        }
    }

    // --- Configuração dos Event Listeners ---

    ELEMENTS.fabTrigger?.addEventListener("click", (e) => { e.preventDefault(); openPanel(); });
    ELEMENTS.closeButton?.addEventListener("click", closePanel);
    ELEMENTS.overlay?.addEventListener("click", closePanel);
    ELEMENTS.ragForm?.addEventListener("submit", handleFormSubmit);

    // Adiciona funcionalidade aos botões de sugestão
    ELEMENTS.suggestionButtons.forEach(button => {
        button.addEventListener("click", function () {
            ELEMENTS.perguntaInput.value = this.textContent;
            ELEMENTS.ragForm.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
        });
    });
});