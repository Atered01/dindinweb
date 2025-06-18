function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    if (menu) menu.classList.toggle('hidden');
}

// Correção do cache para o botão "voltar" do navegador
window.addEventListener('pageshow', function(event) {
    if (event.persisted) window.location.reload();
});

// Executa o código principal quando o HTML estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    
    // --- LÓGICA DE TROCA DE TEMA (COM PADRÃO CLARO) ---
    const themeSwitcher = document.getElementById('theme-switcher');
    const darkThemeLink = document.createElement('link');
    darkThemeLink.rel = 'stylesheet';
    // Assumindo que a BASE_URL não está disponível no JS, usamos um caminho absoluto fixo
    darkThemeLink.href = '/Dindinweb/css/dark-theme.css'; 

    // Função para aplicar o tema
    function applyTheme(theme) {
        document.body.classList.remove('dark-theme', 'light-theme');
        
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            document.head.appendChild(darkThemeLink);
            localStorage.setItem('theme', 'dark');
        } else {
            document.body.classList.add('light-theme');
            if (document.head.contains(darkThemeLink)) {
                document.head.removeChild(darkThemeLink);
            }
            localStorage.setItem('theme', 'light');
        }
    }

    // =======================================================
    // CORREÇÃO APLICADA AQUI
    // Define 'light' como padrão se nenhum tema estiver salvo.
    // =======================================================
    const initialTheme = localStorage.getItem('theme') || 'light';
    
    // Aplica o tema inicial ao carregar a página
    applyTheme(initialTheme);

    // Adiciona o evento de clique no botão para alternar
    if (themeSwitcher) {
        themeSwitcher.addEventListener('click', (event) => {
            event.preventDefault(); 
            let currentTheme = localStorage.getItem('theme');
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });
    }


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

    // --- LÓGICA DE SCROLLSPY E LINK ATIVO ---
    const navLinks = document.querySelectorAll('.nav-links a');
    const sections = document.querySelectorAll('main section[id]');
    const currentURL = window.location.href;

    function updateActiveLink() {
        let currentSectionId = '';

        // Encontra a seção que está mais proeminente na tela
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (window.scrollY >= sectionTop - 150) { // O '- 150' ajuda a ativar o link um pouco antes
                currentSectionId = section.getAttribute('id');
            }
        });

        let linkWasActivated = false;

        navLinks.forEach(link => {
            link.classList.remove('active');
            const linkHref = link.getAttribute('href');

            // Se uma seção está ativa, ativa o link correspondente
            if (currentSectionId && linkHref.includes('#' + currentSectionId)) {
                link.classList.add('active');
                linkWasActivated = true;
            }
        });

        // Se, após verificar todas as seções, nenhum link de âncora foi ativado,
        // ativa o link da página principal.
        if (!linkWasActivated) {
            const currentPageFile = window.location.pathname.split('/').pop() || 'index.php';
            navLinks.forEach(link => {
                // Ativa o link se ele corresponde à página atual E não é um link para uma seção
                if (link.getAttribute('href').endsWith(currentPageFile) && !link.href.includes('#')) {
                    link.classList.add('active');
                } else if ((currentPageFile === '' || currentPageFile === 'index.php') && (link.href.includes('homeSemLogin') || link.href.includes('homeComLogin'))) {
                     if(!link.href.includes('#')) {
                         link.classList.add('active');
                     }
                }
            });
        }
    }

    // Executa a função ao carregar a página e a cada evento de rolagem
    if (navLinks.length > 0) {
        updateActiveLink(); // Executa uma vez no carregamento
        window.addEventListener('scroll', updateActiveLink);
    }
});