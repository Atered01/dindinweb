<?php
// Em Dindinweb/templates/tornar_admin.php
require_once('../PHP/config.php');

// Guarda de segurança para garantir que apenas um admin possa executar esta ação
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Pega o ID do usuário a ser alterado, que vem da URL (ex: ...?id=5)
$id_usuario_para_alterar = $_GET['id'] ?? null;
// Pega o ID do admin que está logado
$id_admin_logado = $_SESSION['usuario_id'];

// Validação para garantir que um ID foi fornecido
if (!$id_usuario_para_alterar) {
    die("ID do usuário não fornecido.");
}

// Regra de negócio: um admin não pode remover seu próprio privilégio
if ($id_usuario_para_alterar == $id_admin_logado) {
    die("Você não pode alterar seu próprio status de administrador por segurança.");
}

try {
    // 1. Descobre o status atual (is_admin) do usuário no banco
    $stmt = $pdo->prepare("SELECT is_admin FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario_para_alterar]);
    $status_atual = $stmt->fetchColumn();

    // Se o usuário não for encontrado, interrompe
    if ($status_atual === false) {
        die("Usuário não encontrado.");
    }

    // 2. Inverte o status: se for 1 (true) vira 0 (false), e vice-versa
    $novo_status = !$status_atual;

    // 3. Atualiza o banco de dados com o novo status
    $update_stmt = $pdo->prepare("UPDATE usuarios SET is_admin = ? WHERE id = ?");
    $update_stmt->execute([$novo_status, $id_usuario_para_alterar]);

    // 4. Redireciona de volta para a página de administração
    header('Location: ../templates/admin_home.php');
    exit();

} catch (PDOException $e) {
    die("Erro ao alterar status do usuário: " . $e->getMessage());
}
?>