<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinDin Verde - Cashback Sustentável</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/saiba-mais.css">
</head>

<body>
    <?php
    if (file_exists('../includes/header_publico.php')) {
        include '../includes/header_publico.php';
    }
    ?>

    <main>
        <section class="hero-section">
            <div class="container hero-container">
                <div class="hero-text">
                    <h1 class="hero-title">Somos a DinDin Verde!</h1>
                    <p class="hero-subtitle">Startup de impacto que revaloriza as embalagens da sua marca, gera impacto ambiental positivo para o seu negócio e fideliza seus clientes de forma sustentável e divertida!</p>
                    <div class="hero-buttons">
                        <a href="cadastro.php" class="btn btn-light">Comece agora <i class="fas fa-arrow-right"></i></a>
                        <a href="#how-it-works" class="btn btn-outline-light">Como funciona</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="../img/pessoas_reciclando.png" alt="Pessoas reciclando">
                </div>
            </div>
        </section>

        <?php include '../includes/sobre_nos_section.php'; ?>

        <section class="features-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Nossos Pilares de Atuação</h2>
                    <p class="section-subtitle">Tecnologias inovadoras para um impacto socioambiental positivo</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper"><i class="fas fa-leaf feature-icon"></i></div>
                        <h3 class="feature-title">Tecnologias Verdes</h3>
                        <p class="feature-description">Ciência, tecnologia e inovação com alto potencial de impacto socioambiental.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-wrapper"><i class="fas fa-database feature-icon"></i></div>
                        <h3 class="feature-title">Dados</h3>
                        <p class="feature-description">Big Data, Blockchain, IA (Inteligência Artificial), IoT (Internet das Embalagens), Código QR, ESG Analytics.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-wrapper"><i class="fas fa-lightbulb feature-icon"></i></div>
                        <h3 class="feature-title">Inovação</h3>
                        <p class="feature-description">Soluções sustentáveis e inovadoras para preservar o meio ambiente e promover o desenvolvimento socioeconômico.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon-wrapper"><i class="fas fa-paint-brush feature-icon"></i></div>
                        <h3 class="feature-title">Criatividade</h3>
                        <p class="feature-description">Compromisso sustentável com diversão e colaboração com a moeda ambiental DinDin Verde.</p>
                    </div>
                </div>
            </div>
        </section>



        <section id="how-it-works" class="how-it-works-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Como Funciona</h2>
                    <p class="section-subtitle">A jornada sustentável com DinDin Verde em 4 passos simples.</p>
                </div>
                <div class="timeline">
                    <div class="timeline-step">
                        <div class="timeline-step-number">1</div>
                        <h3 class="timeline-step-title">Cadastre-se</h3>
                        <p class="timeline-step-description">Faça seu cadastro na plataforma e baixe o app.</p>
                    </div>
                    <div class="timeline-step">
                        <div class="timeline-step-number">2</div>
                        <h3 class="timeline-step-title">Colete embalagens</h3>
                        <p class="timeline-step-description">Junte embalagens de produtos participantes.</p>
                    </div>
                    <div class="timeline-step">
                        <div class="timeline-step-number">3</div>
                        <h3 class="timeline-step-title">Leve ao ponto</h3>
                        <p class="timeline-step-description">Localize um ponto de coleta e entregue os itens.</p>
                    </div>
                    <div class="timeline-step">
                        <div class="timeline-step-number">4</div>
                        <h3 class="timeline-step-title">Ganhe DinDin Verde</h3>
                        <p class="timeline-step-description">Receba créditos e use como cashback!</p>
                    </div>
                </div>
            </div>
        </section>


        <?php include '../includes/saiba_mais_section.php'; ?>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/scripts.js"></script>
</body>

</html>