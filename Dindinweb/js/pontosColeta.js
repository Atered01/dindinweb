// Em Dindinweb/js/pontos_coleta.js

document.addEventListener('DOMContentLoaded', function() {

    const cepInput = document.getElementById('cepInput');
    const buscarCepBtn = document.getElementById('buscarCepBtn');
    const mapaContainer = document.getElementById('mapaContainer');
    const listaPontos = document.getElementById('listaPontos');

    if (!cepInput) return;

    const pontosDeColeta = [
        { name: 'Supermercado Verde Faccini', endereco: 'Av. Paulo Faccini, 1500, Macedo, Guarulhos, SP' },
        { name: 'Parque Shopping Maia Ponto Verde', endereco: 'Av. Bartolomeu de Carlos, 230, Jardim Flor da Montanha, Guarulhos, SP' },
        { name: 'Bosque Maia Coleta Seletiva', endereco: 'Av. Papa João XXIII, 219, Parque Renato Maia, Guarulhos, SP' },
        { name: 'Carrefour Vila Rio', endereco: 'Av. Benjamin Harris Hunicutt, 1999, Vila Rio de Janeiro, Guarulhos, SP' },
        { name: 'Hipermercado Extra - Pimentas', endereco: 'Estr. Juscelino Kubtischek de Oliveira, 5308, Jardim Albertina, Guarulhos, SP' },
        { name: 'Atacadão Guarulhos', endereco: 'Av. Monteiro Lobato, 1137, Macedo, Guarulhos, SP' }
    ];

    // Máscara de CEP (existente)
    cepInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.slice(0, 5) + '-' + value.slice(5, 8);
        }
        e.target.value = value;
    });

    // =======================================================
    // NOVA FUNCIONALIDADE: BUSCAR COM A TECLA "ENTER"
    // =======================================================
    cepInput.addEventListener('keydown', function(event) {
        // Verifica se a tecla pressionada foi a 'Enter' (código 13)
        if (event.key === 'Enter') {
            // Impede o comportamento padrão do Enter (como submeter um formulário)
            event.preventDefault();
            // Simula um clique no botão de busca
            buscarCepBtn.click();
        }
    });


    // Clique no botão "Buscar" (existente)
    buscarCepBtn.addEventListener('click', () => {
        const cep = cepInput.value.replace(/\D/g, '');
        if (cep.length !== 8) {
            alert('Por favor, digite um CEP válido com 8 dígitos.');
            return;
        }
        
        mapaContainer.innerHTML = '<p>Buscando endereço...</p>';
        listaPontos.innerHTML = '<p>Carregando pontos...</p>';

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na rede ou CEP inválido');
                }
                return response.json();
            })
            .then(data => {
                if (data.erro) {
                    mapaContainer.innerHTML = '<p>CEP não encontrado. Tente novamente.</p>';
                    listaPontos.innerHTML = '<p>Não foi possível encontrar pontos de coleta.</p>';
                    return;
                }
                const enderecoCompleto = `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                atualizarMapa(enderecoCompleto);
                gerarPontosAleatorios();
            })
            .catch(error => {
                console.error('Erro ao buscar CEP:', error);
                mapaContainer.innerHTML = '<p style="color: red;">Não foi possível buscar o CEP. Verifique sua conexão com a internet.</p>';
                listaPontos.innerHTML = '';
            });
    });

    // Clique na lista de pontos (existente)
    listaPontos.addEventListener('click', function(event) {
        const pontoClicado = event.target.closest('.ponto');
        if (!pontoClicado) return;
        const enderecoDoPonto = pontoClicado.dataset.address;
        if (enderecoDoPonto) {
            atualizarMapa(enderecoDoPonto);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Funções auxiliares (existentes)
    function gerarPontosAleatorios() {
        listaPontos.innerHTML = '';
        const pontosParaExibir = pontosDeColeta.sort(() => 0.5 - Math.random()).slice(0, 4);

        pontosParaExibir.forEach(ponto => {
            if (!ponto || typeof ponto.name === 'undefined' || typeof ponto.endereco === 'undefined') {
                console.error("DEBUG: Um dos objetos em 'pontosDeColeta' está malformado.", ponto);
                return; 
            }
            const distanciaKm = (Math.random() * 15 + 1).toFixed(1);
            const aberto = Math.random() < 0.7;
            const statusHtml = aberto ? '<p>🟢 Aberto agora • 08:00–19:00</p>' : '<p>⚪ Fechado agora</p>';
            
            const pontoHtml = `
                <div class="ponto" data-address="${ponto.endereco}" style="cursor: pointer;">
                    <div class="info">
                        <strong>${ponto.name}</strong>
                        <p>${ponto.endereco.split(',').slice(0, 2).join(', ')}</p> 
                        ${statusHtml}
                    </div>
                    <span class="distancia">${distanciaKm} km</span>
                </div>
            `;
            listaPontos.innerHTML += pontoHtml;
        });
    }

    function atualizarMapa(endereco) {
        const urlMapa = `https://maps.google.com/maps?q=$${encodeURIComponent(endereco)}&output=embed&z=15`;
        mapaContainer.innerHTML = `<iframe width="100%" height="100%" style="border:0;" loading="lazy" allowfullscreen src="${urlMapa}"></iframe>`;
    }
});