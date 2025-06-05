// static/js/login_script.js
document.addEventListener('DOMContentLoaded', function() {
    console.log("login_script.js carregado e DOM pronto!"); // Para depuração

    const loginForm = document.getElementById('loginForm');
    const messageDiv = document.getElementById('message');

    if (loginForm) {
        console.log("Formulário #loginForm encontrado. Adicionando listener..."); // Para depuração
        loginForm.addEventListener('submit', async function(event) {
            event.preventDefault(); // ESSENCIAL para impedir o envio padrão
            console.log("Envio do formulário interceptado pelo JS!"); // Para depuração

            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;

            const credentials = {
                email: email,
                senha: senha
            };

            const apiUrl = 'http://127.0.0.1:5000/auth/login'; // Sua API de login

            if(messageDiv) {
                messageDiv.innerHTML = '';
                messageDiv.className = '';
            }

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(credentials)
                });

                const result = await response.json();

                if (response.ok) {
                    if (result.access_token) {
                        localStorage.setItem('accessToken', result.access_token);
                        if(messageDiv) {
                            messageDiv.textContent = 'Login bem-sucedido!';
                            messageDiv.classList.add('success');
                        }
                        loginForm.reset();
                        alert('Login realizado com sucesso! Token armazenado.');
                        // Opcional: Redirecionar
                        // window.location.href = '/alguma-pagina-protegida.html';
                    } else {
                        if(messageDiv) {
                            messageDiv.textContent = result.msg || 'Token não encontrado na resposta.';
                            messageDiv.classList.add('error');
                        }
                    }
                } else {
                    if(messageDiv) {
                        messageDiv.textContent = result.msg || 'Email ou senha inválidos.';
                        messageDiv.classList.add('error');
                    }
                }
            } catch (error) {
                console.error('Erro na requisição de login:', error);
                if(messageDiv) {
                    messageDiv.textContent = 'Não foi possível conectar ao servidor.';
                    messageDiv.classList.add('error');
                }
            }
        });
    } else {
        console.warn('Elemento #loginForm não encontrado na página de login.');
    }
});