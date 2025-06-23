<?php
// Em Dindinweb/PHP/alterar_senha.php

require_once('config.php'); // Inicia a sessão e conecta ao DB

// Garante que o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

// Garante que o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/templates/configuracoes.php');
    exit();
}

// 1. Coleta e valida os dados do formulário
$id_usuario = $_SESSION['usuario_id'];
$senha_atual = $_POST['senha_atual'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';
$confirmar_nova_senha = $_POST['confirmar_nova_senha'] ?? '';

if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_nova_senha)) {
    $_SESSION['erro_senha'] = "Todos os campos são obrigatórios.";
    header('Location: ../templates/configuracoes.php');
    exit();
}

if (strlen($nova_senha) < 6) {
    $_SESSION['erro_senha'] = "A nova senha deve ter pelo menos 6 caracteres.";
    header('Location: ../templates/configuracoes.php');
    exit();
}

if ($nova_senha !== $confirmar_nova_senha) {
    $_SESSION['erro_senha'] = "As novas senhas não coincidem.";
    header('Location: ../templates/configuracoes.php');
    exit();
}

try {
    // 2. Verifica se a senha atual está correta
    $stmt = $pdo->prepare("SELECT senha_hash FROM usuarios WHERE id_personalizado = ?");
    $stmt->execute([$id_usuario]);
    $hash_banco = $stmt->fetchColumn();

    if (!$hash_banco || !password_verify($senha_atual, $hash_banco)) {
        $_SESSION['erro_senha'] = "A senha atual está incorreta.";
        header('Location: ../templates/configuracoes.php');
        exit();
    }

    // 3. Se a senha atual estiver correta, atualiza para a nova senha
    $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    
    $update_stmt = $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE id_personalizado = ?");
    $update_stmt->execute([$novo_hash, $id_usuario]);

    $_SESSION['sucesso_senha'] = "Senha alterada com sucesso!";
    header('Location: ../templates/configuracoes.php');
    exit();

} catch (PDOException $e) {
    error_log("Erro ao alterar senha: " . $e->getMessage());
    $_SESSION['erro_senha'] = "Ocorreu um erro no sistema ao tentar alterar a senha. Tente novamente.";
    header('Location: ../templates/configuracoes.php');
    exit();
}
?>