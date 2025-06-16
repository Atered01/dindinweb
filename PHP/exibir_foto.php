<?php
// Em Dindinweb/templates/exibir_foto.php

// Inclui o config.php, que já inicia a sessão e a conexão PDO
require_once('../PHP/config.php');

// Define o ID do usuário a ser exibido
$id_usuario_para_exibir = null;

// Se um ID for passado na URL E o usuário logado for um admin...
if (isset($_GET['id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    // ... então usa o ID da URL para exibir o perfil de outro usuário.
    $id_usuario_para_exibir = filter_var($_GET['id'], FILTER_VALIDATE_INT);
} 
// Senão, se um usuário comum estiver logado...
elseif (isset($_SESSION['usuario_id'])) {
    // ... usa o ID da própria sessão.
    $id_usuario_para_exibir = $_SESSION['usuario_id'];
}

// Se não houver nenhum ID para exibir, interrompe
if (!$id_usuario_para_exibir) {
    http_response_code(401); // Unauthorized
    exit();
}

try {
    // Busca os dados da imagem (BLOB) do banco de dados usando o ID
    $sql = "SELECT foto_perfil FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario_para_exibir]);
    
    // fetchColumn(0) pega o valor da primeira coluna diretamente como uma string
    $dados_imagem = $stmt->fetchColumn(0);

    if ($dados_imagem) {
        // Detecta o tipo MIME da imagem a partir do seu conteúdo binário
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->buffer($dados_imagem);
        
        // Envia o cabeçalho correto para o tipo de imagem
        header('Content-Type: ' . $mime_type);
        
        // Imprime os dados binários da imagem para o navegador
        echo $dados_imagem;
    } else {
        // Se o usuário não tiver foto, exibe uma imagem padrão.
        // Verifique se a imagem 'default.png' existe na pasta /uploads/
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