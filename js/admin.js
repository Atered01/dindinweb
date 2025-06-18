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

    // --- GRÁFICO 2: TOTAL DE ITENS RECICLADOS POR MÊS ---
    const itensChartCanvas = document.getElementById('itensRecicladosChart');
    if (itensChartCanvas && typeof DADOS_GRAFICO_ITENS !== 'undefined') {
        const ctxItens = itensChartCanvas.getContext('2d');
        new Chart(ctxItens, {
            type: 'line',
            data: {
                labels: DADOS_GRAFICO_ITENS.labels, // Meses
                datasets: [{
                    label: 'Total de Itens Reciclados',
                    data: DADOS_GRAFICO_ITENS.data, // Soma de itens
                    backgroundColor: 'rgba(66, 165, 245, 0.2)',
                    borderColor: 'rgba(25, 118, 210, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});