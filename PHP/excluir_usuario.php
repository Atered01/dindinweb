<?php
// Em Dindinweb/templates/excluir_usuario.php
require_once('../PHP/config.php');

// Guarda de segurança do admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Pega o ID da URL
$id_usuario_para_excluir = $_GET['id'] ?? null;
$id_admin_logado = $_SESSION['usuario_id'];

// Validações
if (!$id_usuario_para_excluir) {
    die("ID do usuário não fornecido.");
}

// Regra de negócio: um admin não pode excluir a si mesmo
if ($id_usuario_para_excluir == $id_admin_logado) {
    die("Você não pode excluir sua própria conta de administrador.");
}

try {
    // O ON DELETE CASCADE no banco de dados cuidará de apagar as estatísticas
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario_para_excluir]);
    
    // Redireciona de volta para a página de admin
    header('Location: admin_home.php');
    exit();

} catch (PDOException $e) {
    die("Erro ao excluir usuário: " . $e->getMessage());
}
?>