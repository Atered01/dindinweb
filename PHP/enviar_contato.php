<?php
// Em Dindinweb/PHP/enviar_contato.php

// Define que a resposta será SEMPRE em JSON, mesmo se houver um erro de PHP
header('Content-Type: application/json');

// Função para enviar a resposta e terminar o script de forma segura
function responder($sucesso, $mensagem) {
    echo json_encode(['success' => $sucesso, 'message' => $mensagem]);
    exit();
}

// O require_once vem DEPOIS da definição da função e do header, 
// para que possamos capturar erros de inclusão.
// Usando o PROJECT_ROOT definido no config para um caminho à prova de falhas.
// Primeiro, precisamos definir PROJECT_ROOT se o config falhar.
$projectRoot = dirname(__DIR__);
$configFile = $projectRoot . '/PHP/config.php';

if (!file_exists($configFile)) {
    responder(false, 'Erro crítico: Arquivo de configuração não encontrado.');
}
require_once($configFile);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Coleta e valida os dados do formulário
    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING));
    $email_remetente = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $assunto_selecionado = trim(filter_input(INPUT_POST, 'assunto', FILTER_SANITIZE_STRING));
    $mensagem = trim(filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_STRING));

    if (empty($nome) || !$email_remetente || empty($assunto_selecionado) || empty($mensagem)) {
        responder(false, 'Todos os campos são obrigatórios e o e-mail deve ser válido.');
    }

    // 2. Monta o e-mail
    $destinatario = "fernandoluisjasse21@gmail.com";
    $assunto_email = "Contato Site (DinDin Verde) - " . $assunto_selecionado;
    
    $corpo_email = "Você recebeu uma nova mensagem do formulário de contato.\n\n";
    $corpo_email .= "Nome: " . $nome . "\n";
    $corpo_email .= "E-mail: " . $email_remetente . "\n";
    $corpo_email .= "Assunto: " . $assunto_selecionado . "\n\n";
    $corpo_email .= "Mensagem:\n" . $mensagem . "\n";

    $headers = "From: noreply@dindinverde.com\r\n" . // Use um e-mail do seu domínio
               "Reply-To: " . $email_remetente . "\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // 3. Envia o e-mail
    if (@mail($destinatario, $assunto_email, $corpo_email, $headers)) {
        responder(true, 'Sua mensagem foi enviada com sucesso!');
    } else {
        // O @ antes de mail() suprime os warnings do PHP, permitindo-nos enviar nosso próprio erro JSON
        responder(false, 'Erro no servidor ao enviar o e-mail. Tente novamente mais tarde.');
    }
} else {
    responder(false, 'Acesso inválido.');
}
?>