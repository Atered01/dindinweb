<?php
// CORREÇÃO: Inclui o config.php no topo para definir a BASE_URL
require_once('../PHP/config.php');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pontos de Coleta - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/pontos_coleta.css" />
</head>

<body>
    <?php
    // Lógica para exibir o cabeçalho correto
    if (isset($_SESSION['usuario_id'])) {
        include '../includes/header_logado.php';
    } else {
        include '../includes/header_publico.php';
    }
    ?>

    <main class="container page-pontos-coleta">
        <h1>Pontos de Coleta</h1>
        <p class="subtitulo">Encontre os locais mais próximos para reciclar suas embalagens e ganhar DinDin Verde</p>

        <div class="conteudo">
            <div class="mapa" id="mapaContainer">
                <div class="mapa-placeholder">
                    <img src="https://cdn-icons-png.flaticon.com/512/854/854878.png" alt="Ícone Mapa" class="icone-mapa">
                    <p>Digite um CEP e clique em buscar para ver o mapa</p>
                </div>
            </div>

            <aside class="painel-lateral">
                <label for="cepInput">Onde você está?</label>
                <input type="text" id="cepInput" placeholder="Digite seu CEP" maxlength="9">
                <button id="buscarCepBtn" class="botao-verde">Buscar Pontos</button>
                <div id="enderecoResultado"></div>
                <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #eee;">
                <h2>Pontos mais próximos</h2>
                <div id="listaPontos">
                    <p style="color: #666; font-size: 0.9rem;">Busque por um CEP para ver os pontos de coleta.</p>
                </div>
            </aside>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="<?php echo BASE_URL; ?>../PHP/index.php" style="text-decoration: none; color: #1b8e3e; font-weight: bold;">← Voltar para o início</a>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>../js/pontosColeta.js"></script>
    <script src="<?php echo BASE_URL; ?>../js/scripts.js"></script>
</body>
</html>