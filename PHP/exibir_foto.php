<?php
require_once('../PHP/config.php');

$id_usuario_para_exibir = null;

// Se for admin e um ID personalizado for passado via GET
if (isset($_GET['id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    $id_usuario_para_exibir = preg_match('/^[A-Za-z0-9_-]+$/', $_GET['id']) ? $_GET['id'] : null;
} 
// Senão, usa o ID personalizado da própria sessão
elseif (isset($_SESSION['usuario_id'])) {
    $id_usuario_para_exibir = $_SESSION['usuario_id'];
}

// Falha se nenhum ID válido foi definido
if (!$id_usuario_para_exibir) {
    http_response_code(401);
    exit();
}

try {
    $sql = "SELECT foto_perfil FROM usuarios WHERE id_personalizado = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario_para_exibir]);

    $dados_imagem = $stmt->fetchColumn();

    if ($dados_imagem) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($dados_imagem);
        header('Content-Type: ' . $mime_type);
        echo $dados_imagem;
    } else {
        $caminho_padrao = '../uploads/default.png';
        if (file_exists($caminho_padrao)) {
            header('Content-Type: image/png');
            readfile($caminho_padrao);
        } else {
            http_response_code(404);
        }
    }

} catch (PDOException $e) {
    error_log("Erro ao exibir foto: " . $e->getMessage());
    http_response_code(500);
}
?>
