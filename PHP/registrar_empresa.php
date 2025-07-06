<?php
// Em Dindinweb/PHP/registrar_empresa.php
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../templates/cadastro_empresa.php');
    exit();
}

// 1. Coleta e validação básica dos dados
$nome_empresa = trim($_POST['nome_empresa'] ?? '');
$email = filter_input(INPUT_POST, 'email_contato', FILTER_VALIDATE_EMAIL);
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

if (empty($nome_empresa) || !$email || empty($senha)) {
    $_SESSION['erro_cadastro_empresa'] = "Todos os campos são obrigatórios.";
    header('Location: ../templates/cadastro_empresa.php');
    exit();
}

if (strlen($senha) < 6) {
    $_SESSION['erro_cadastro_empresa'] = "A senha deve ter pelo menos 6 caracteres.";
    header('Location: ../templates/cadastro_empresa.php');
    exit();
}

if ($senha !== $confirmar_senha) {
    $_SESSION['erro_cadastro_empresa'] = "As senhas não coincidem.";
    header('Location: ../templates/cadastro_empresa.php');
    exit();
}

// 2. Verifica se o e-mail já está cadastrado
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE email_contato = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['erro_cadastro_empresa'] = "Este e-mail já está cadastrado em nosso sistema.";
        header('Location: ../templates/cadastro_empresa.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Erro ao verificar e-mail de empresa: " . $e->getMessage());
    $_SESSION['erro_cadastro_empresa'] = "Erro no servidor. Tente novamente.";
    header('Location: ../templates/cadastro_empresa.php');
    exit();
}

// 3. Se tudo estiver certo, insere no banco
try {
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO empresas (nome_empresa, email_contato, senha_hash) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome_empresa, $email, $senha_hash]);

    $_SESSION['mensagem_sucesso'] = "Empresa cadastrada com sucesso! Faça o login para acessar o portal.";
    header('Location: ../templates/empresa_login.php');
    exit();

} catch (PDOException $e) {
    error_log("Erro ao inserir empresa: " . $e->getMessage());
    $_SESSION['erro_cadastro_empresa'] = "Não foi possível concluir o cadastro. Tente novamente mais tarde.";
    header('Location: ../templates/cadastro_empresa.php');
    exit();
}
?>