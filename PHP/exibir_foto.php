<?php
// Em Dindinweb/PHP/exibir_foto.php

require_once('../PHP/config.php'); // O config.php já inicia a sessão

// Usa o ID numérico da sessão para buscar a foto
$id_usuario = $_SESSION['usuario_id'] ?? null;

if (!$id_usuario) {
    http_response_code(401); // Unauthorized
    exit();
}

try {
    // Busca os dados da imagem (BLOB) do banco de dados
    $sql = "SELECT foto_perfil FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario]);
    
    // =======================================================
    // CORREÇÃO APLICADA AQUI
    // fetchColumn(0) pega o valor da primeira coluna diretamente como uma string
    // =======================================================
    $dados_imagem = $stmt->fetchColumn(0);

    if ($dados_imagem) {
        // Envia o cabeçalho correto para o tipo de imagem
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($dados_imagem);
        header('Content-Type: ' . $mime_type);
        
        // Imprime os dados binários da imagem
        echo $dados_imagem;
    } else {
        // Se o usuário não tiver foto, exibe uma imagem padrão.
        $caminho_padrao = '../uploads/default.png';
        if (file_exists($caminho_padrao)) {
            header('Content-Type: image/png');
            readfile($caminho_padrao);
        }
    }

} catch (PDOException $e) {
    error_log("Erro ao exibir foto: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
}
?>