<?php
require_once('../PHP/config.php');

// Garante que apenas usuários logados possam acessar
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Classificar Embalagem - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/dark-theme.css">
    <style>
        .classifier-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--color-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            text-align: center;
        }
        #webcam-container canvas {
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        #label-container div {
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 0.5rem;
        }
        .start-button {
            background-color: var(--color-primary-dark);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: var(--border-radius);
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header_logado.php'; ?>

    <main class="container">
        <div class="classifier-container">
            <h2>Classificador de Embalagens</h2>
            <p>Aponte a embalagem para a câmera e clique em iniciar para identificá-la.</p>
            
            <button class="start-button" type="button" onclick="init()">Iniciar Câmera</button>
            
            <div id="webcam-container"></div>
            <div id="label-container"></div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@teachablemachine/image@latest/dist/teachablemachine-image.min.js"></script>
    <script type="text/javascript">
        // Link para a pasta do seu modelo que você criou no Passo 1
        const URL = "<?php echo BASE_URL; ?>/my_model/";

        let model, webcam, labelContainer, maxPredictions;

        async function init() {
            const modelURL = URL + "model.json";
            const metadataURL = URL + "metadata.json";

            model = await tmImage.load(modelURL, metadataURL);
            maxPredictions = model.getTotalClasses();

            const flip = true;
            webcam = new tmImage.Webcam(300, 300, flip);
            await webcam.setup();
            await webcam.play();
            window.requestAnimationFrame(loop);

            document.getElementById("webcam-container").appendChild(webcam.canvas);
            labelContainer = document.getElementById("label-container");
            for (let i = 0; i < maxPredictions; i++) {
                labelContainer.appendChild(document.createElement("div"));
            }
        }

        async function loop() {
            webcam.update();
            await predict();
            window.requestAnimationFrame(loop);
        }

        async function predict() {
            const prediction = await model.predict(webcam.canvas);
            for (let i = 0; i < maxPredictions; i++) {
                const classPrediction =
                    prediction[i].className + ": " + prediction[i].probability.toFixed(2);
                labelContainer.childNodes[i].innerHTML = classPrediction;
            }
        }
    </script>
    <script src="<?php echo BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>