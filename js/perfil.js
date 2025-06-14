// Em Dindinweb/js/perfil.js

document.addEventListener('DOMContentLoaded', function () {
    
    // --- LÓGICA DAS ABAS ---
    const tabs = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');
    if (tabs.length > 0 && contents.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                const activeContent = document.querySelector(tab.dataset.target);
                if (activeContent) {
                    activeContent.classList.add('active');
                }
            });
        });
    }

    // --- LÓGICA DE NÍVEL E PROGRESSO (BASEADA NO VALOR TOTAL) ---
    // A variável DADOS_PERFIL é criada no arquivo perfil.php
    if (typeof DADOS_PERFIL !== 'undefined') {
        
        const valorTotalImpacto = DADOS_PERFIL.co2 + DADOS_PERFIL.agua + DADOS_PERFIL.energia;

        const nivelTextoEl = document.getElementById('nivel-texto');
        const progressoBarraEl = document.getElementById('progresso-barra');
        const progressoTextoEl = document.getElementById('progresso-texto');

        const NIVEIS = {
            INICIANTE: { min: 0, max: 5000, nome: 'Reciclador Iniciante', proximo: 'Bronze' },
            BRONZE: { min: 5000, max: 15000, nome: 'Reciclador Bronze', proximo: 'Prata' },
            PRATA: { min: 15000, max: 30000, nome: 'Reciclador Prata', proximo: 'Ouro' },
            OURO: { min: 30000, max: Infinity, nome: 'Reciclador Ouro', proximo: 'Nível Máximo!' }
        };

        let nivelAtual = NIVEIS.INICIANTE;
        let progresso = 0;

        if (valorTotalImpacto >= NIVEIS.OURO.min) nivelAtual = NIVEIS.OURO;
        else if (valorTotalImpacto >= NIVEIS.PRATA.min) nivelAtual = NIVEIS.PRATA;
        else if (valorTotalImpacto >= NIVEIS.BRONZE.min) nivelAtual = NIVEIS.BRONZE;

        if (nivelAtual.proximo !== 'Nível Máximo!') {
            const pontosNoNivelAtual = valorTotalImpacto - nivelAtual.min;
            const pontosParaProximoNivel = nivelAtual.max - nivelAtual.min;
            progresso = (pontosNoNivelAtual / pontosParaProximoNivel) * 100;
        } else {
            progresso = 100;
        }
        
        if (nivelTextoEl) nivelTextoEl.textContent = nivelAtual.nome;
        if (progressoTextoEl) progressoTextoEl.textContent = `Progresso para ${nivelAtual.proximo}`;
        if (progressoBarraEl) {
            setTimeout(() => {
                progressoBarraEl.style.width = progresso + '%';
            }, 100);
        }
    }


    // --- LÓGICA DO GRÁFICO (COMPLETA) ---
    const chartCanvas = document.getElementById('impactoChart');
    if (chartCanvas && typeof DADOS_PERFIL !== 'undefined') {
        const ctx = chartCanvas.getContext('2d');
        const { co2, agua, energia } = DADOS_PERFIL;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['CO2 (kg)', 'Água (L)', 'Energia (kWh)'],
                datasets: [{
                    label: 'Impacto Ambiental',
                    data: [co2, agua, energia],
                    backgroundColor: [
                        'rgba(102, 187, 106, 0.7)',
                        'rgba(66, 165, 245, 0.7)',
                        'rgba(255, 202, 40, 0.7)'
                    ],
                    borderColor: [
                        '#66bb6a',
                        '#42a5f5',
                        '#ffca28'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false } 
                },
                scales: { 
                    y: { 
                        type: 'logarithmic',
                        ticks: {
                            callback: function(value) {
                                // Mostra apenas potências de 10 para uma escala mais limpa
                                if (value === 1 || value === 10 || value === 100 || value === 1000 || value === 10000 || value === 100000) {
                                    return value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                weight: 'bold'
                            }
                        }
                    } 
                }
            }
        });
    }
});