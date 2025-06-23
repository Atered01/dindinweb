<?php
// Em Dindinweb/PHP/adicionar_recompensa.php
require_once('config.php');
require_once('helpers.php'); // CORREÇÃO 1: Inclui o arquivo com a função gerarSlugUnico

// Guarda de segurança do admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../templates/gerenciar_recompensas.php');
    exit();
}

// 1. Coleta e valida dados de texto
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$custo_em_ddv = filter_var($_POST['custo_em_ddv'], FILTER_VALIDATE_FLOAT);
$tipo = $_POST['tipo'] ?? '';

if (empty($nome) || $custo_em_ddv === false || empty($tipo)) {
    $_SESSION['erro_recompensa'] = "Por favor, preencha todos os campos obrigatórios.";
    header('Location: ../templates/gerenciar_recompensas.php');
    exit();
}

// 2. Validação do Upload da Imagem
if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['erro_recompensa'] = "Ocorreu um erro com o upload da imagem. Tente novamente.";
    header('Location: ../templates/gerenciar_recompensas.php');
    exit();
}

$imagem = $_FILES['imagem'];
$tamanho_maximo = 2 * 1024 * 1024; // 2 MB
$tipos_permitidos = ['image/jpeg', 'image/png'];

if ($imagem['size'] > $tamanho_maximo) {
    $_SESSION['erro_recompensa'] = "A imagem é muito grande. O limite é de 2MB.";
    header('Location: ../templates/gerenciar_recompensas.php');
    exit();
}

if (!in_array($imagem['type'], $tipos_permitidos)) {
    $_SESSION['erro_recompensa'] = "Formato de imagem inválido. Use apenas JPG ou PNG.";
    header('Location: ../templates/gerenciar_recompensas.php');
    exit();
}

// 3. Salvar a Imagem no Servidor
$nome_arquivo = uniqid() . '-' . basename($imagem['name']);
$caminho_destino = PROJECT_ROOT . '/uploads/recompensas/' . $nome_arquivo;
$url_relativa = '/Dindinweb/uploads/recompensas/' . $nome_arquivo; // Caminho a partir da raiz do servidor

if (!move_uploaded_file($imagem['tmp_name'], $caminho_destino)) {
    $_SESSION['erro_recompensa'] = "Falha ao mover a imagem para o destino.";
    header('Location: ../templates/gerenciar_recompensas.php');
    exit();
}

// Gera o slug ANTES de inserir no banco
$slug = gerarSlugUnico($pdo, 'recompensas', $nome);

// 4. Inserir no Banco de Dados
try {
    // CORREÇÃO 2: Salva o caminho relativo no banco de dados
    $sql = "INSERT INTO recompensas (nome, slug, descricao, custo_em_ddv, tipo, imagem_url) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $slug, $descricao, $custo_em_ddv, $tipo, $url_relativa]);


    $_SESSION['sucesso_recompensa'] = "Nova recompensa adicionada com sucesso!";
    header('Location: ../templates/gerenciar_recompensas.php');
    exit();
} catch (PDOException $e) {
    // Se der erro no BD, apaga o arquivo que já foi salvo
    unlink($caminho_destino);
    $_SESSION['erro_recompensa'] = "Erro ao salvar recompensa no banco de dados.";
    header('Location: ../templates/gerenciar_recompensas.php');
    exit();
}
?>