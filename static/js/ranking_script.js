// static/js/ranking_script.js
document.addEventListener('DOMContentLoaded', function() {
    const rankingBody = document.getElementById('ranking-body');
    const messageDiv = document.getElementById('message');

    async function fetchRanking() {
        const token = localStorage.getItem('accessToken');

        if (!token) {
            messageDiv.textContent = 'Você precisa estar logado para ver o ranking.';
            messageDiv.classList.add('error');
            setTimeout(() => { window.location.href = '/login'; }, 3000);
            return;
        }

        const apiUrl = 'http://127.0.0.1:5000/pontos/ranking';

        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            const rankingData = await response.json();

            if (response.ok) {
                populateTable(rankingData);
            } else {
                messageDiv.textContent = rankingData.msg || 'Não foi possível carregar o ranking.';
                messageDiv.classList.add('error');
            }

        } catch (error) {
            console.error('Erro ao buscar o ranking:', error);
            messageDiv.textContent = 'Erro de conexão ao buscar o ranking.';
            messageDiv.classList.add('error');
        }
    }

    function populateTable(data) {
        // Limpa qualquer conteúdo antigo da tabela
        rankingBody.innerHTML = '';

        if (data.length === 0) {
            rankingBody.innerHTML = '<tr><td colspan="3">Nenhum usuário encontrado.</td></tr>';
            return;
        }

        // Itera sobre a lista de usuários (o array 'data')
        data.forEach((user, index) => {
            // Cria uma nova linha na tabela (<tr>)
            const row = document.createElement('tr');

            // Insere as células (<td>) com os dados
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${user.nome}</td>
                <td>${user.pontuacao}</td>
            `;

            // Adiciona a nova linha ao corpo da tabela
            rankingBody.appendChild(row);
        });
    }

    // Chama a função para buscar os dados quando a página carrega
    fetchRanking();
});