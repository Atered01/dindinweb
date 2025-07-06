<?php
require_once('config.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Configurações de rate limiting
$MAX_REQUESTS_PER_DAY = 100; // Limite diário
$RATE_LIMIT_WINDOW = 60; // Janela de rate limiting em segundos
$MIN_REQUEST_INTERVAL = 1; // Intervalo mínimo entre requisições em segundos

// Inicializa a sessão se não existir
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handler para requisições OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Configuração de timezone e tratamento de erros
date_default_timezone_set('America/Sao_Paulo');
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Verificação de autenticação
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'] && !isset($_SESSION['empresa_id'])) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado. Faça login como administrador ou empresa.']);
    exit();
}

// Inicializa o sistema de tracking de uso
if (!isset($_SESSION['api_usage'])) {
    $_SESSION['api_usage'] = [
        'daily_count' => 0,
        'first_request_time' => time(),
        'last_request_time' => time(),
        'window_count' => 0,
        'window_start' => time()
    ];
}

// Verificação do limite diário
if ($_SESSION['api_usage']['daily_count'] >= $MAX_REQUESTS_PER_DAY) {
    $reset_time = strtotime('tomorrow midnight');
    header('X-RateLimit-Limit: ' . $MAX_REQUESTS_PER_DAY);
    header('X-RateLimit-Remaining: 0');
    header('X-RateLimit-Reset: ' . $reset_time);
    header('Retry-After: ' . ($reset_time - time()));
        
    http_response_code(429);
    echo json_encode([
        'erro' => 'Limite diário de requisições atingido',
        'limite' => $MAX_REQUESTS_PER_DAY,
        'reset_time' => $reset_time,
        'requests_remaining' => 0
    ]);
    exit();
}

// Verificação de rate limiting por janela de tempo
$current_time = time();
$agora = $current_time;
$espera = $MIN_REQUEST_INTERVAL;

// Verificação adicional de rate limiting com sessão
if (isset($_SESSION['ultima_chamada_ia']) && ($agora - $_SESSION['ultima_chamada_ia'] < $espera)) {
    $tempo_restante = $espera - ($agora - $_SESSION['ultima_chamada_ia']);
    http_response_code(429);
    header('Retry-After: ' . $tempo_restante);
    echo json_encode([
        'erro' => 'Limite de taxa excedido',
        'retry_after' => $tempo_restante,
        'limite' => $MAX_REQUESTS_PER_DAY,
        'reset_time' => strtotime('tomorrow midnight'),
        'requests_remaining' => $MAX_REQUESTS_PER_DAY - $_SESSION['api_usage']['daily_count']
    ]);
    exit();
}

// Atualize a última chamada da IA
$_SESSION['ultima_chamada_ia'] = $agora;

$time_since_last = $current_time - $_SESSION['api_usage']['last_request_time'];

// Verifica intervalo mínimo entre requisições
if ($time_since_last < $MIN_REQUEST_INTERVAL) {
    header('Retry-After: ' . ($MIN_REQUEST_INTERVAL - $time_since_last));
    http_response_code(429);
    echo json_encode([
        'erro' => 'Aguarde um pouco antes de enviar uma nova pergunta.',
        'tempo_restante' => ($MIN_REQUEST_INTERVAL - $time_since_last)
    ]);
    exit();
}

// Verifica se a janela de rate limiting expirou
if (($current_time - $_SESSION['api_usage']['window_start']) > $RATE_LIMIT_WINDOW) {
    $_SESSION['api_usage']['window_start'] = $current_time;
    $_SESSION['api_usage']['window_count'] = 0;
}

// Verifica limite de requisições na janela atual
if ($_SESSION['api_usage']['window_count'] >= 10) { // Exemplo: 10 requisições por minuto
    $retry_after = $RATE_LIMIT_WINDOW - ($current_time - $_SESSION['api_usage']['window_start']);
    header('Retry-After: ' . $retry_after);
    http_response_code(429);
    echo json_encode([
        'erro' => 'Muitas requisições em um curto período',
        'retry_after' => $retry_after
    ]);
    exit();
}

// Atualiza o contador de uso
$_SESSION['api_usage']['daily_count']++;
$_SESSION['api_usage']['window_count']++;
$_SESSION['api_usage']['last_request_time'] = $current_time;

// Headers de rate limiting para o cliente
header('X-RateLimit-Limit: ' . $MAX_REQUESTS_PER_DAY);
header('X-RateLimit-Remaining: ' . ($MAX_REQUESTS_PER_DAY - $_SESSION['api_usage']['daily_count']));
header('X-RateLimit-Reset: ' . strtotime('tomorrow midnight'));

// ==============================================
// VALIDAÇÃO DO CORPO DA REQUISIÇÃO
// ==============================================
$corpoRequisicao = file_get_contents('php://input');
if (empty($corpoRequisicao)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Corpo da requisição vazio']);
    exit();
}

$dadosRequisicao = json_decode($corpoRequisicao, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['erro' => 'JSON inválido no corpo da requisição']);
    exit();
}

// ==============================================
// VARIÁVEIS DA REQUISIÇÃO
// ==============================================
$prompt = $dadosRequisicao['prompt'] ?? '';
$sqlQuery = $dadosRequisicao['query'] ?? '';
$perguntaOriginal = $dadosRequisicao['pergunta'] ?? 'Pergunta não fornecida';
$id_usuario_log = $_SESSION['usuario_id'] ?? ($_SESSION['empresa_id'] ?? 'desconhecido');

// ==============================================
// CAMINHO 1: CHAMADA À API GEMINI
// ==============================================
if (!empty($prompt)) {
    // Verifica se a chave da API está configurada
    $gemini_api_key = getenv('GEMINI_API_KEY');
    if ($gemini_api_key === false) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro de configuração no servidor (GEMINI_API_KEY não definida)']);
        exit();
    }
    
    $gemini_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $gemini_api_key;
        
    $post_data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 2000,
            'temperature' => 0.7
        ]
    ];

    $tentativas = 0;
    $max_tentativas = 2;
    $respostaGemini = null;
    $http_code = 500;

    while ($tentativas < $max_tentativas) {
        $tentativas++;
        try {
            $ch = curl_init($gemini_url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($post_data),
                CURLOPT_TIMEOUT => 15, // 15 segundos de timeout
                CURLOPT_CONNECTTIMEOUT => 5
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_error) {
                throw new Exception("Erro cURL: " . $curl_error);
            }

            if ($http_code === 200) {
                $respostaJson = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Resposta da Gemini não é JSON válido");
                }

                // Verifica estrutura da resposta
                if (!isset($respostaJson['candidates'][0]['content']['parts'][0]['text'])) {
                    throw new Exception("Estrutura da resposta da Gemini inválida");
                }

                echo $respostaJson['candidates'][0]['content']['parts'][0]['text'];
                exit();
            }

            if ($http_code !== 429) {
                throw new Exception("Resposta da API Gemini com status $http_code");
            }

            // Se foi 429, espera antes de tentar novamente
            sleep(1);

        } catch (Exception $e) {
            error_log("Erro na chamada Gemini (Tentativa $tentativas): " . $e->getMessage());
            if ($tentativas >= $max_tentativas) {
                http_response_code($http_code >= 400 ? $http_code : 500);
                echo json_encode([
                    'erro' => 'Erro ao processar resposta da IA',
                    'detalhes' => $e->getMessage(),
                    'codigo' => $e->getCode()
                ]);
                exit();
            }
        }
    }

    // Se todas as tentativas falharem
    http_response_code(429);
    echo json_encode(['erro' => 'Muitas solicitações para a IA. Tente novamente em alguns segundos.']);
    exit();
}

// ==============================================
// CAMINHO 2: CONSULTA SQL
// ==============================================
if (empty($sqlQuery)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Nenhum prompt ou consulta SQL foi fornecido.']);
    exit();
}

// Validação de segurança da consulta SQL
if (preg_match('/^\s*SELECT/i', $sqlQuery) !== 1) {
    http_response_code(403);
    echo json_encode(['erro' => 'Apenas consultas SELECT são permitidas.']);
    exit();
}

// Bloqueia consultas potencialmente perigosas
$blacklist = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'GRANT', 'REVOKE'];
foreach ($blacklist as $keyword) {
    if (stripos($sqlQuery, $keyword) !== false) {
        http_response_code(403);
        echo json_encode(['erro' => 'Consulta SQL contém operação não permitida.']);
        exit();
    }
}

// ==============================================
// EXECUÇÃO DA CONSULTA SQL
// ==============================================
$log_sucesso = false;
$dados = [];
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $stmt = $pdo->prepare($sqlQuery);
    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $log_sucesso = true;

    // Validação dos dados
    if (!is_array($dados)) {
        throw new PDOException("Formato de dados inválido retornado");
    }

    echo json_encode([
        'sucesso' => true,
        'dados' => $dados,
        'metadata' => [
            'linhas' => count($dados),
            'timestamp' => time()
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro na RAG API (SQL): " . $e->getMessage());
    echo json_encode([
        'erro' => 'Erro ao executar a consulta no banco de dados',
        'codigo' => $e->getCode(),
        'mensagem' => $e->getMessage(),
        'sqlstate' => $e->errorInfo[0] ?? null
    ]);

} finally {
    // Logging da consulta (mesmo se der erro)
    try {
        $sql_log = "INSERT INTO log_rag (
            id_personalizado_usuario, 
            pergunta, 
            sql_gerado, 
            sucesso,
            timestamp,
            ip_origem
        ) VALUES (?, ?, ?, ?, ?, ?)";
                
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->execute([
            $id_usuario_log,
            substr($perguntaOriginal, 0, 500),
            substr($sqlQuery, 0, 2000),
            (int)$log_sucesso,
            time(),
            $_SERVER['REMOTE_ADDR'] ?? 'desconhecido'
        ]);

    } catch (PDOException $log_e) {
        error_log("Falha ao gravar log do RAG: " . $log_e->getMessage());
    }
}
?>
