// Em Dindinweb/js/scripts.js

// Lógica para o menu mobile
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

// Correção do cache para o botão "voltar" do navegador
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});


document.addEventListener('DOMContentLoaded', function () {
    
    // --- LÓGICA DO DROPDOWN DO USUÁRIO ---
    const dropdownToggle = document.getElementById('dropdown-toggle');
    const dropdownMenu = document.getElementById('dropdown-menu');
    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            dropdownMenu.classList.toggle('show');
            this.classList.toggle('active');
        });
    }
    // Fecha o menu se o usuário clicar fora dele
    window.addEventListener('click', function (event) {
        if (dropdownMenu && dropdownToggle && dropdownMenu.classList.contains('show') && !dropdownToggle.contains(event.target)) {
            dropdownMenu.classList.remove('show');
            dropdownToggle.classList.remove('active');
        }
    });


    // --- LÓGICA FINAL E CORRIGIDA: SCROLLSPY E LINK ATIVO ---
    const navLinks = document.querySelectorAll('.nav-links a');
    const sections = document.querySelectorAll('main section[id]');

    function updateActiveLink() {
        let activeSectionId = null;
        
        // Determina qual seção está visível na tela
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (window.scrollY >= sectionTop - 150) { // O '- 150' ajuda a ativar o link um pouco antes de chegar na seção
                activeSectionId = section.getAttribute('id');
            }
        });

        let linkWasActivated = false;

        navLinks.forEach(link => {
            link.classList.remove('active');
            // Se encontramos uma seção ativa, e o link aponta para ela, ativamos o link
            if (activeSectionId && link.href.includes('#' + activeSectionId)) {
                link.classList.add('active');
                linkWasActivated = true;
            }
        });

        // Se, após verificar todas as seções, nenhum link foi ativado (estamos no topo),
        // ativamos o link da página principal.
        if (!linkWasActivated) {
            const currentPageFile = window.location.pathname.split('/').pop() || 'index.php';
            navLinks.forEach(link => {
                // Ativa o link se ele corresponde à página atual E não é um link para uma seção
                if (link.href.includes(currentPageFile) && !link.href.includes('#')) {
                    link.classList.add('active');
                } else if ((currentPageFile === 'index.php' || currentPageFile === '') && (link.href.includes('homeSemLogin') || link.href.includes('homeComLogin'))) {
                     if(!link.href.includes('#')){
                        link.classList.add('active');
                     }
                }
            });
        }
    }

    // Executa a função ao carregar a página e a cada evento de rolagem
    if (navLinks.length > 0) {
        updateActiveLink(); // Executa uma vez no carregamento
        window.addEventListener('scroll', updateActiveLink); // Executa ao rolar
    }
});