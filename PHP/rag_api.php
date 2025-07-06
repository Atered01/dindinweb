<?php
// Carrega a configuração e as dependências
require_once 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use GeminiAPI\Resources\Parts\TextPart;

// --- CONFIGURAÇÃO E CABEÇALHOS ---
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// --- HANDLER PARA REQUISIÇÕES OPTIONS (CORS) ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// --- INICIALIZAÇÃO E SEGURANÇA ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');

// Verificação de autenticação
if (!isset($_SESSION['is_admin']) && !isset($_SESSION['empresa_id'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado. Faça o login para continuar.']);
    exit();
}

// --- DECODIFICAÇÃO DA REQUISIÇÃO ---
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['erro' => 'JSON inválido no corpo da requisição.']);
    exit();
}

$prompt = $input['prompt'] ?? null;
$sqlQuery = $input['query'] ?? null;

// --- ROTEAMENTO DA REQUISIÇÃO ---

// ROTA 1: Executar consulta SQL no banco de dados
if ($sqlQuery) {
    try {
        $stmt = $pdo->prepare($sqlQuery);
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['dados' => $dados]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Erro no banco de dados (RAG API): " . $e->getMessage());
        echo json_encode(['erro' => 'Erro ao consultar o banco de dados.']);
    }
    exit();
}

// ROTA 2: Chamar a IA
if ($prompt) {
    try {
        $gemini_api_key = getenv('GEMINI_API_KEY');
        if (!$gemini_api_key) {
            throw new Exception("Chave da API não configurada no servidor.");
        }
        
        $client = new \GeminiAPI\Client($gemini_api_key);
        $textPart = new TextPart($prompt);

        // **CORREÇÃO FINAL APLICADA**
        // Usando o nome exato do modelo da sua lista: 'gemini-1.5-flash'
        $response = $client->generativeModel('gemini-1.5-flash')
                           ->generateContent($textPart);
        
        echo json_encode(['conteudo' => $response->text()]);

    } catch (Exception $e) {
        http_response_code(500);
        error_log("Erro na chamada da IA (RAG API): " . $e->getMessage());
        echo json_encode(['erro' => 'Erro ao se comunicar com a IA.', 'detalhes' => $e->getMessage()]);
    }
    exit();
}

// --- RESPOSTA PARA REQUISIÇÃO INVÁLIDA ---
http_response_code(400);
echo json_encode(['erro' => 'Requisição inválida. Forneça um "prompt" ou uma "query".']);