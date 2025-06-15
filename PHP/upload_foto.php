<?php
// Em Dindinweb/templates/upload_foto.php

require_once('../PHP/config.php'); // O config.php já inicia a sessão

if (!isset($_SESSION['usuario_id'])) { 
    header('Location: ' . BASE_URL . '/templates/login.php'); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
    
    $foto = $_FILES['foto_perfil'];
    $id_usuario = $_SESSION['usuario_id'];

    if ($foto['size'] > 2 * 1024 * 1024) { // Limite de 2MB
        die("Erro: O arquivo é muito grande (limite de 2MB).");
    }

    // Lê o conteúdo binário do arquivo enviado
    $conteudo_imagem = file_get_contents($foto['tmp_name']);

    // Salva o conteúdo binário no banco de dados
    try {
        $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        // O terceiro parâmetro, PDO::PARAM_LOB, é crucial para tratar dados BLOB
        $stmt->bindParam(1, $conteudo_imagem, PDO::PARAM_LOB);
        $stmt->bindParam(2, $id_usuario);
        
        $stmt->execute();

        header('Location: ' . BASE_URL . '/templates/perfil.php');
        exit();

    } catch (PDOException $e) {
        die("Erro ao salvar no banco de dados: " . $e->getMessage());
    }

} else {
    header('Location: ' . BASE_URL . '/templates/perfil.php?erro_upload=1');
    exit();
}
?>