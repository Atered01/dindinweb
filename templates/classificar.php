<?php
require_once('../PHP/config.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classificar Embalagem - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/classificador.css">
</head>

<body>
    <?php include '../includes/header_logado.php'; ?>

    <main class="container">
        <div class="classifier-container">
            <h2>Classificador de Embalagens</h2>
            <p>Aponte a embalagem para a câmera e clique em iniciar para identificá-la.</p>

            <button id="startButton" class="start-button" type="button">Iniciar Câmera</button>

            <div id="webcam-container"></div>
            <div id="label-container"></div>
            <button id="descartarBtn" class="descartar-button" style="display: none;" onclick="descartarItem()">Descartar Item</button>
            <div id="status-message"></div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@teachablemachine/image@latest/dist/teachablemachine-image.min.js"></script>
    <script type="text/javascript">
        const MODEL_URL = "<?php echo BASE_URL; ?>/my_model/";
    </script>
    <script src="<?php echo BASE_URL; ?>/js/classificador.js"></script>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>

</html>