// Garante que o script só rode depois que o HTML estiver completamente carregado e parseado.
document.addEventListener('DOMContentLoaded', function() { 
    const loginForm = document.getElementById('loginForm');
    const messageDiv = document.getElementById('message');

    // Verifica se o formulário realmente existe na página antes de adicionar o listener
    if (loginForm) { 
        loginForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;

            const credentials = {
                email: email,
                senha: senha
            };

            // Ajuste a URL e a porta se necessário (de acordo com seu app.py)
            const apiUrl = 'http://127.0.0.1:5000/auth/login'; 

            messageDiv.innerHTML = '';
            messageDiv.className = '';

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

                        messageDiv.textContent = 'Login bem-sucedido!';
                        messageDiv.classList.add('success');
                        loginForm.reset();

                        alert('Login realizado com sucesso! Token armazenado.');
                        // Opcional: Redirecionar
                        // window.location.href = '/dashboard.html'; 
                    } else {
                        messageDiv.textContent = result.msg || 'Token não encontrado na resposta.';
                        messageDiv.classList.add('error');
                    }
                } else {
                    messageDiv.textContent = result.msg || 'Email ou senha inválidos.';
                    messageDiv.classList.add('error');
                }
            } catch (error) {
                console.error('Erro na requisição de login:', error);
                messageDiv.textContent = 'Não foi possível conectar ao servidor. Tente novamente mais tarde.';
                messageDiv.classList.add('error');
            }
        });
    } else {
        console.warn('Elemento #loginForm não encontrado na página.');
    }
});