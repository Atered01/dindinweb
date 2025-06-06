// static/js/pontos_script.js
document.addEventListener('DOMContentLoaded', function() {
    const pointsSpan = document.getElementById('user-points');
    const messageDiv = document.getElementById('message');
    const logoutButton = document.getElementById('logout-button');

    // Função para buscar os pontos na API
    async function fetchUserPoints() {
        // 1. Recuperar o token do localStorage
        const token = localStorage.getItem('accessToken');

        // 2. Verificar se o token existe
        if (!token) {
            pointsSpan.textContent = '???';
            messageDiv.textContent = 'Você precisa fazer login para ver seus pontos.';
            messageDiv.classList.add('error');
            // Opcional: redirecionar para a página de login após um tempo
            setTimeout(() => {
                window.location.href = '/login'; // Ajuste se a sua rota de login for outra
            }, 3000);
            return; // Para a execução da função aqui
        }

        // 3. Se o token existe, fazer a chamada para a API protegida
        const apiUrl = 'http://127.0.0.1:5000/pontos'; // Rota GET /pontos do backend

        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    // Adiciona o token ao cabeçalho de autorização
                    'Authorization': `Bearer ${token}`
                }
            });

            const result = await response.json();

            if (response.ok) {
                // 4. Se a resposta for bem-sucedida, exibe os pontos
                pointsSpan.textContent = result.pontuacao;
            } else {
                // 5. Se a resposta for um erro (ex: 401 token expirado), trata o erro
                pointsSpan.textContent = 'Erro';
                messageDiv.textContent = result.msg || 'Sua sessão expirou. Por favor, faça login novamente.';
                messageDiv.classList.add('error');
                
                // Limpa o token inválido/expirado e redireciona
                localStorage.removeItem('accessToken');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 3000);
            }
        } catch (error) {
            // Lida com erros de rede
            console.error('Erro ao buscar pontos:', error);
            pointsSpan.textContent = 'Erro';
            messageDiv.textContent = 'Não foi possível conectar ao servidor.';
            messageDiv.classList.add('error');
        }
    }

    // Função de Logout
    function handleLogout() {
        // Remove o token do localStorage
        localStorage.removeItem('accessToken');
        alert('Você foi desconectado.');
        // Redireciona para a página de login
       // window.location.href = '/login';
    }

    // Adiciona o evento de clique ao botão de logout
    if (logoutButton) {
        logoutButton.addEventListener('click', handleLogout);
    }

    // Chama a função para buscar os pontos quando a página carrega
    fetchUserPoints();
});