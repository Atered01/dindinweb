// O atributo 'defer' na tag <script> já garante que o DOM está pronto,
// mas usar DOMContentLoaded é uma prática robusta adicional, especialmente se 'defer' for removido.
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const messageDiv = document.getElementById('message'); // Para exibir mensagens de feedback

    // Só executa o código do formulário de registro se o formulário existir na página atual
    if (registerForm) {
        registerForm.addEventListener('submit', async function(event) {
            event.preventDefault(); // Impede o envio padrão do formulário

            // Coleta os valores dos campos
            const nome = document.getElementById('nome').value;
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;

            // Prepara os dados para enviar como JSON
            const userData = {
                nome: nome,
                email: email,
                senha: senha
            };

            // URL da sua API de registro no backend
            // Certifique-se de que esta é a rota correta no seu auth.py
            const apiUrl = 'http://127.0.0.1:5000/auth/register';

            // Limpa mensagens anteriores
            if(messageDiv) { // Verifica se messageDiv existe
                messageDiv.innerHTML = '';
                messageDiv.className = ''; // Remove classes de estilo anteriores
            }


            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(userData)
                });

                const result = await response.json(); // Tenta converter a resposta para JSON

                if (response.ok) { // Status HTTP 2xx indica sucesso
                    if(messageDiv){
                        messageDiv.textContent = result.msg || 'Usuário registrado com sucesso!';
                        messageDiv.classList.add('success');
                    }
                    registerForm.reset(); // Limpa os campos do formulário
                    alert('Cadastro realizado com sucesso! Agora você pode fazer login.'); // Feedback adicional
                } else {
                    // Se o servidor retornou um erro (status 4xx, 5xx)
                    if(messageDiv){
                        messageDiv.textContent = result.msg || 'Erro ao registrar usuário. Verifique os dados e tente novamente.';
                        messageDiv.classList.add('error');
                    }
                }
            } catch (error) {
                // Erro de rede ou se response.json() falhar (ex: resposta não é JSON válido)
                console.error('Erro durante o processo de cadastro:', error);
                if(messageDiv){
                    messageDiv.textContent = 'Não foi possível conectar ao servidor ou ocorreu um erro inesperado.';
                    messageDiv.classList.add('error');
                }
            }
        });
    } else {
        // Se este script.js for usado em outras páginas que não têm o registerForm,
        // esta mensagem pode aparecer no console, o que é normal e esperado.
        console.log("Elemento #registerForm não encontrado nesta página.");
    }
});