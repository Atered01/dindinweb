<?php
// Arquivo: rag_api.php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['is_admin']) && !isset($_SESSION['empresa_id'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado. Faça o login.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? null;
$modelName = 'mistral:instruct';

function chamarOllamaAPI($prompt, $model) {
    $url = 'http://127.0.0.1:11434/api/generate';
    $data = [
        'model' => $model,
        'prompt' => $prompt,
        'stream' => false
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception("Erro cURL: " . curl_error($ch));
    }
    curl_close($ch);

    $parsed = json_decode($response, true);
    return $parsed['response'] ?? '';
}

function extrairSqlDeTexto($texto) {
    if (preg_match('/```sql(.*?)```/is', $texto, $m)) {
        return trim($m[1]);
    }
    if (preg_match('/^(SELECT|INSERT|UPDATE|DELETE).*?;/ims', $texto, $m)) {
        return trim($m[0]);
    }
    return null;
}

if ($prompt) {
    try {
        $respostaIA = chamarOllamaAPI($prompt, $modelName);
        $sqlQuery = extrairSqlDeTexto($respostaIA);

        if ($sqlQuery) {
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->execute();
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            echo json_encode([
                'resposta' => "Consulta realizada com sucesso:",
                'dados' => $dados,
                'debug_info' => ['sqlGerado' => $sqlQuery]
            ]);
        } else {
            echo json_encode([
                'resposta' => $respostaIA,
                'debug_info' => ['observacao' => 'Nenhuma consulta SQL extraída.']
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'erro' => 'Erro ao executar a requisição.',
            'detalhes' => $e->getMessage(),
        ]);
    }
    exit();
}

http_response_code(400);
echo json_encode(['erro' => 'Requisição inválida.']);
