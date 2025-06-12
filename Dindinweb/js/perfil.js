// Em Dindinweb/js/perfil.js

document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove a classe 'active' de todas as abas e conteúdos
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            // Adiciona a classe 'active' à aba clicada
            tab.classList.add('active');

            // Adiciona a classe 'active' ao conteúdo correspondente
            const activeContent = document.querySelector(tab.dataset.target);
            if (activeContent) {
                activeContent.classList.add('active');
            }
        });
    });
});