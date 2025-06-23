// Em Dindinweb/js/admin.js

document.addEventListener('DOMContentLoaded', function() {
    
    // --- GRÁFICO 1: CADASTROS POR MÊS ---
    const cadastrosChartCanvas = document.getElementById('cadastrosChart');
    if (cadastrosChartCanvas && typeof DADOS_GRAFICO_CADASTROS !== 'undefined') {
        const ctxCadastros = cadastrosChartCanvas.getContext('2d');
        new Chart(ctxCadastros, {
            type: 'line',
            data: {
                labels: DADOS_GRAFICO_CADASTROS.labels,
                datasets: [{
                    label: 'Novos Usuários',
                    data: DADOS_GRAFICO_CADASTROS.data,
                    backgroundColor: 'rgba(102, 187, 106, 0.2)',
                    borderColor: 'rgba(56, 142, 60, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        ticks: { 
                            stepSize: 1,
                            callback: function(value) { if (Number.isInteger(value)) { return value; } }
                        } 
                    } 
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    // --- GRÁFICO 2: TOP 10 RECICLADORES ---
    const itensChartCanvas = document.getElementById('itensRecicladosChart');
    if (itensChartCanvas && typeof DADOS_GRAFICO_ITENS !== 'undefined') {
        const ctxItens = itensChartCanvas.getContext('2d');
        new Chart(ctxItens, {
            type: 'bar', // Mudei para 'bar' para melhor visualização dos nomes
            data: {
                labels: DADOS_GRAFICO_ITENS.labels,
                datasets: [{
                    label: 'Total de Itens Reciclados',
                    data: DADOS_GRAFICO_ITENS.data,
                    backgroundColor: 'rgba(66, 165, 245, 0.7)',
                    borderColor: 'rgba(25, 118, 210, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // Coloca o gráfico na horizontal
                plugins: { 
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Itens Reciclados por Usuário'
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    // A CHAVE "}" QUE FALTAVA ESTÁ AQUI. FECHANDO O BLOCO DO GRÁFICO 2

    // --- GRÁFICO 3: RECOMPENSAS MAIS POPULARES ---
    const recompensasChartCanvas = document.getElementById('recompensasChart');
    if (recompensasChartCanvas && typeof DADOS_GRAFICO_RECOMPENSAS !== 'undefined') {
        const ctxRecompensas = recompensasChartCanvas.getContext('2d');
        new Chart(ctxRecompensas, {
            type: 'bar',
            data: {
                labels: DADOS_GRAFICO_RECOMPENSAS.labels,
                datasets: [{
                    label: 'Total de Resgates',
                    data: DADOS_GRAFICO_RECOMPENSAS.data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Número de Vezes que Cada Recompensa foi Resgatada'
                    }
                }
            }
        });
    }
});