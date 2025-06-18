// Em Dindinweb/js/contato.js

document.addEventListener('DOMContentLoaded', function() {
    const contatoForm = document.getElementById('form-contato');
    
    // Função para criar e mostrar o pop-up
    function showPopup(isSuccess, message) {
        // Remove qualquer pop-up antigo
        const oldPopup = document.getElementById('contato-popup');
        if (oldPopup) oldPopup.remove();

        // Cria os elementos do pop-up
        const overlay = document.createElement('div');
        overlay.id = 'contato-popup';
        overlay.className = 'modal-overlay';

        const content = document.createElement('div');
        content.className = 'modal-content';

        const iconClass = isSuccess ? 'fas fa-check-circle success' : 'fas fa-times-circle error';
        const title = isSuccess ? 'Enviado!' : 'Ocorreu um Erro';

        content.innerHTML = `
            <div class="icon ${isSuccess ? 'success' : 'error'}"><i class="${iconClass}"></i></div>
            <h3>${title}</h3>
            <p>${message}</p>
            <button class="btn btn-primary">Fechar</button>
        `;

        overlay.appendChild(content);
        document.body.appendChild(overlay);

        // Mostra o pop-up com animação
        setTimeout(() => overlay.classList.add('show'), 10);

        // Adiciona evento para fechar
        const closeButton = content.querySelector('button');
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay || closeButton.contains(e.target)) {
                overlay.classList.remove('show');
                // Remove o pop-up do HTML após a animação
                setTimeout(() => overlay.remove(), 300);
            }
        });
    }


    if (contatoForm) {
        contatoForm.addEventListener('submit', function(event) {
            event.preventDefault(); 
            const submitBtn = contatoForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.textContent;
            
            submitBtn.textContent = 'Enviando...';
            submitBtn.disabled = true;

            const formData = new FormData(contatoForm);

            fetch(contatoForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showPopup(true, data.message);
                    contatoForm.reset(); 
                } else {
                    showPopup(false, data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showPopup(false, 'Erro de rede. Tente novamente.');
            })
            .finally(() => {
                submitBtn.textContent = originalBtnText;
                submitBtn.disabled = false;
            });
        });
    }
});