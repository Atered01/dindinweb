<?php

// Habilita a visualização de erros para diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Carrega o autoloader do Composer
require_once __DIR__ . '/../vendor/autoload.php';

echo "<pre>"; // Para formatar a saída de forma legível

try {
    // Carrega a chave da API (verifique se seu .env está na pasta Dindinweb/)
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    $gemini_api_key = $_ENV['GEMINI_API_KEY'];

    if (!$gemini_api_key) {
        throw new Exception("Chave da API GEMINI_API_KEY não encontrada no arquivo .env.");
    }

    echo "Chave da API encontrada. Conectando à Gemini...\n\n";

    // Inicializa o cliente
    $client = new \GeminiAPI\Client($gemini_api_key);

    // **CHAMA A FUNÇÃO PARA LISTAR OS MODELOS**
    $models = $client->listModels();

    echo "Modelos disponíveis para sua chave de API:\n";
    echo "========================================\n\n";

    // Imprime os modelos encontrados
    foreach ($models as $model) {
        print_r($model);
        echo "\n----------------------------------------\n";
    }

} catch (Exception $e) {
    echo "Ocorreu um erro: " . $e->getMessage();
}

echo "</pre>";