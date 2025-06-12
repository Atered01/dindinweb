// Em Dindinweb/js/scripts.js

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

    // --- LÓGICA PARA FECHAR DROPDOWN AO CLICAR FORA ---
    window.addEventListener('click', function (event) {
        if (dropdownMenu && dropdownToggle && dropdownMenu.classList.contains('show') && !dropdownToggle.contains(event.target)) {
            dropdownMenu.classList.remove('show');
            dropdownToggle.classList.remove('active');
        }
    });

    // --- LÓGICA NOVA: SCROLLSPY PARA O MENU ATIVO ---
    const navLinks = document.querySelectorAll('.nav-links a');
    const sections = document.querySelectorAll('section[id]');

    // Primeiro, destaca o link da página atual (lógica simplificada)
    const paginaAtual = window.location.pathname.split('/').pop();
    navLinks.forEach(link => {
        if (link.getAttribute('href').includes(paginaAtual)) {
            link.classList.add('active');
        }
    });

    // Função que será chamada quando uma seção entrar ou sair da tela
    const onSectionIntersect = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Remove 'active' de todos os links
                navLinks.forEach(link => link.classList.remove('active'));
                
                // Adiciona 'active' ao link correspondente à seção visível
                const id = entry.target.getAttribute('id');
                const activeLink = document.querySelector(`.nav-links a[href*="#${id}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            }
        });
    };

    // Cria o "observador" com as opções
    // O threshold: 0.5 significa que a função será chamada quando 50% da seção estiver visível
    const observer = new IntersectionObserver(onSectionIntersect, {
        root: null, // Observa em relação à viewport inteira
        threshold: 0.5,
    });

    // Pede para o observador monitorar cada uma das seções
    sections.forEach(section => {
        observer.observe(section);
    });
});

// --- LÓGICA DO MENU MOBILE (EXISTENTE) ---
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

// --- CORREÇÃO DO CACHE (EXISTENTE) ---
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});