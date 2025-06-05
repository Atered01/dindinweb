document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const messageDiv = document.getElementById('message');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            const nome = document.getElementById('nome').value;
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const userData = {
                nome: nome,
                email: email,
                senha: senha
            };

            const apiUrl = 'http://127.0.0.1:5000/auth/register';

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
                    body: JSON.stringify(userData)
                });

                const result = await response.json();

                if (response.ok) {
                    if(messageDiv){
                        messageDiv.textContent = result.msg || 'Usuário registrado com sucesso!';
                        messageDiv.classList.add('success');
                    }
                    registerForm.reset();
                    alert('Cadastro realizado com sucesso! Agora você pode fazer login.');
                } else {
                    
                    if(messageDiv){
                        messageDiv.textContent = result.msg || 'Erro ao registrar usuário. Verifique os dados e tente novamente.';
                        messageDiv.classList.add('error');
                    }
                }
            } catch (error) {
                
                console.error('Erro durante o processo de cadastro:', error);
                if(messageDiv){
                    messageDiv.textContent = 'Não foi possível conectar ao servidor ou ocorreu um erro inesperado.';
                    messageDiv.classList.add('error');
                }
            }
        });
    } else {
    
        console.log("Elemento #registerForm não encontrado nesta página.");
    }
});