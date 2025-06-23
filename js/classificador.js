// Em Dindinweb/js/classificador.js

// Garante que o código só rode depois que a página carregar completamente
document.addEventListener('DOMContentLoaded', () => {
    // Pega os elementos do HTML
    const startButton = document.getElementById('startButton');
    const descartarBtn = document.getElementById('descartarBtn');
    const webcamContainer = document.getElementById('webcam-container');
    const labelContainer = document.getElementById('label-container');
    const statusMessage = document.getElementById('status-message');

    // Sai se os elementos não existirem na página
    if (!startButton || !descartarBtn) {
        return;
    }
    
    // A variável MODEL_URL é definida no arquivo classificar.php
    const URL = MODEL_URL; 
    let model, webcam, maxPredictions;
    let itemDetectado = null;
    let podeDescartar = true;

    // Adiciona o evento de clique ao botão Iniciar
    startButton.addEventListener('click', () => init());
    // Adiciona o evento de clique ao botão Descartar
    descartarBtn.addEventListener('click', () => descartarItem());

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

        webcamContainer.innerHTML = '';
        webcamContainer.appendChild(webcam.canvas);
        labelContainer.innerHTML = ''; // Limpa labels antigos
        for (let i = 0; i < maxPredictions; i++) {
            labelContainer.appendChild(document.createElement("div"));
        }
    }

    async function loop() {
        if (webcam && webcam.canvas) {
            webcam.update();
            await predict();
        }
        window.requestAnimationFrame(loop);
    }

    async function predict() {
        if (!model) return;
        const prediction = await model.predict(webcam.canvas);
        let itemEncontrado = false;

        for (let i = 0; i < maxPredictions; i++) {
            const classPrediction = prediction[i].className + ": " + prediction[i].probability.toFixed(2);
            if (labelContainer.childNodes[i]) {
                labelContainer.childNodes[i].innerHTML = classPrediction;
            }

            if (prediction[i].probability > 0.97 && podeDescartar) {
                descartarBtn.style.display = 'inline-block';
                itemDetectado = prediction[i].className;
                itemEncontrado = true;
            }
        }

        if (!itemEncontrado) {
            descartarBtn.style.display = 'none';
            itemDetectado = null;
        }
    }

    async function descartarItem() {
        if (!itemDetectado || !podeDescartar) return;

        podeDescartar = false;
        descartarBtn.disabled = true;
        statusMessage.style.color = 'orange';
        statusMessage.innerText = 'Processando descarte...';

        const formData = new FormData();
        formData.append('item_detectado', itemDetectado);

        try {
            const response = await fetch('../PHP/descartar_item.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                statusMessage.style.color = 'var(--color-green)';
            } else {
                statusMessage.style.color = 'var(--color-red)';
            }
            statusMessage.innerText = result.message;

        } catch (error) {
            statusMessage.style.color = 'var(--color-red)';
            statusMessage.innerText = 'Erro de comunicação com o servidor.';
        }

        setTimeout(() => {
            descartarBtn.style.display = 'none';
            descartarBtn.disabled = false;
            statusMessage.innerText = '';
            podeDescartar = true;
        }, 3000);
    }
});