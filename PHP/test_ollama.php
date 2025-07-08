<?php
// Habilita a exibição de todos os erros para um diagnóstico completo
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "<h1>Teste de Conexão com o Servidor Ollama</h1>";

// --- VERIFICAÇÃO 1: Extensão cURL ---
echo "<h2>Passo 1: Verificando se a extensão cURL está ativa...</h2>";
if (function_exists('curl_init')) {
    echo "<p style='color:green;'>Status: OK! A extensão cURL está habilitada no seu PHP.</p>";
} else {
    echo "<p style='color:red;'>ERRO: A extensão cURL não está habilitada no seu php.ini.</p>";
    echo "<p><b>Solução:</b> Abra seu arquivo php.ini (pelo painel do XAMPP: Apache -> Config -> php.ini), procure pela linha <code>;extension=curl</code> e remova o ponto e vírgula (;) do início. Salve o arquivo e reinicie o Apache.</p>";
    exit;
}

// --- VERIFICAÇÃO 2: Tentativa de Conexão ---
$ollama_url = 'http://127.0.0.1:11434/api/tags'; // Usaremos um endpoint diferente que apenas lista os modelos

echo "\n<h2>Passo 2: Tentando conectar ao Ollama em " . htmlspecialchars($ollama_url) . "...</h2>";

// Inicializa o cURL
$ch = curl_init();

// Configura as opções do cURL
curl_setopt($ch, CURLOPT_URL, $ollama_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna a resposta como string em vez de imprimir
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Tempo máximo para conectar
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Tempo máximo total da requisição

// Executa a requisição
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error_num = curl_errno($ch);
$curl_error_msg = curl_error($ch);

// Fecha a conexão cURL
curl_close($ch);


// --- VERIFICAÇÃO 3: Analisando o Resultado ---
echo "\n<h2>Passo 3: Analisando o resultado da conexão...</h2>";

if ($curl_error_num > 0) {
    echo "<p style='color:red;'>ERRO DE CONEXÃO cURL (Código: " . $curl_error_num . "): " . htmlspecialchars($curl_error_msg) . "</p>";
    echo "<p><b>O que isso significa:</b> O PHP não conseguiu estabelecer uma comunicação com o endereço <code>http://127.0.0.1:11434</code>. Isso quase sempre indica um bloqueio de rede.</p>";
    echo "<p><b>Próximo Passo:</b> O problema muito provavelmente é o Firewall do Windows. Mesmo que você já tenha verificado, tente desativá-lo <strong>temporariamente</strong> (Firewall do Windows -> Ativar ou desativar o Firewall do Windows -> Desativar para rede privada) e rode este script novamente. Se funcionar, você precisará criar uma regra de saída específica para o Apache (httpd.exe).</p>";
} else {
    echo "<p style='color:green;'>Status da Conexão: SUCESSO! O PHP conseguiu se comunicar com o servidor Ollama.</p>";
    echo "<p><b>Código de Status HTTP recebido:</b> " . $http_code . "</p>";
    echo "<p><b>Resposta do Servidor:</b></p>";
    echo "<div>" . htmlspecialchars($response) . "</div>";

    if ($http_code == 200) {
        echo "<p style='color:green; font-weight:bold; font-size: 1.2em;'>DIAGNÓSTICO FINAL: SUCESSO TOTAL!</p>";
        echo "<p>A comunicação entre PHP e Ollama está perfeita. O problema anterior estava relacionado ao uso da função `file_get_contents`. O código final do `rag_api.php` que usa cURL deve funcionar.</p>";
    } else {
         echo "<p style='color:orange;'>AVISO: A conexão foi feita, mas o Ollama retornou um código de erro HTTP. A resposta acima pode dar mais detalhes.</p>";
    }
}

echo "</pre>";

?>