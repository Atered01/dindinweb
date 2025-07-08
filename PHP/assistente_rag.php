<?php
// Arquivo: PHP/assistente_rag.php

/**
 * Chama o Ollama com um prompt e retorna a resposta da IA
 */
function chamarOllamaAPI(string $prompt, string $modelo = 'mistral:instruct'): string {
    $data = [
        'model' => $modelo,
        'prompt' => $prompt,
        'stream' => false,
    ];

    $ch = curl_init('http://localhost:11434/api/generate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data),
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    $dados = json_decode($res, true);
    return $dados['response'] ?? 'Erro: Sem resposta da IA.';
}

/**
 * Gera um relatório operacional baseado nos dados reais do banco de dados
 */
function gerarRelatorioOperacional(PDO $pdo): string {
    $usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $descartes = $pdo->query("SELECT COUNT(*) FROM descartes")->fetchColumn();
    $empresas = $pdo->query("SELECT COUNT(*) FROM empresas")->fetchColumn();
    $ddvTotal = $pdo->query("SELECT SUM(saldo_ddv) FROM estatisticas_usuario")->fetchColumn();
    $recompensas = $pdo->query("SELECT COUNT(*) FROM recompensas")->fetchColumn();

    $prompt = <<<EOT
Gere um relatório administrativo para a plataforma DinDin Verde com os seguintes dados:
- Usuários: $usuarios
- Empresas parceiras: $empresas
- Descartes realizados: $descartes
- DDV em circulação: $ddvTotal
- Recompensas cadastradas: $recompensas

Organize o relatório em: Visão Geral, Indicadores, Recomendações e Conclusão.
EOT;

    return chamarOllamaAPI($prompt);
}

/**
 * Transforma pergunta em linguagem natural em SQL e executa no banco de dados
 */
function responderPerguntaLivre(string $pergunta, string $usuario_id, PDO $pdo): string {
    $prompt_sql = <<<EOT
Você é um assistente que converte perguntas em linguagem natural para SQL. Use as tabelas abaixo:

- usuarios(id_personalizado, nome, email, saldo_ddv, ...)
- estatisticas_usuario(usuario_id_personalizado, co2_evitado, saldo_ddv, ...)
- produtos(id, nome_produto, co2_evitado, agua_economizada, energia_poupada, ...)
- descartes(usuario_id_personalizado, produto_id, data_descarte)

Pergunta: "$pergunta"
Usuário: $usuario_id

SQL:
EOT;

    $sql_gerada = chamarOllamaAPI($prompt_sql);

    try {
        $stmt = $pdo->prepare($sql_gerada);
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $json_dados = json_encode($dados, JSON_PRETTY_PRINT);

        $explicacao = chamarOllamaAPI(<<<EOT
Explique o seguinte resultado de banco de dados para o usuário:

$json_dados
EOT);

        $log = $pdo->prepare("INSERT INTO log_rag (id_personalizado_usuario, pergunta, sql_gerado, sucesso) VALUES (?, ?, ?, ?)");
        $log->execute([$usuario_id, $pergunta, $sql_gerada, 1]);

        return $explicacao;
    } catch (PDOException $e) {
        $log = $pdo->prepare("INSERT INTO log_rag (id_personalizado_usuario, pergunta, sql_gerado, sucesso) VALUES (?, ?, ?, ?)");
        $log->execute([$usuario_id, $pergunta, $sql_gerada, 0]);

        return "Erro ao executar a consulta: " . $e->getMessage();
    }
}
