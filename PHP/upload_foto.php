<?php
require_once('../PHP/config.php'); // Já inicia a sessão

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {

    $foto = $_FILES['foto_perfil'];
    $id_usuario = $_SESSION['usuario_id']; // Deve conter o id_personalizado

    if ($foto['size'] > 2 * 1024 * 1024) {
        die("Erro: O arquivo é muito grande (limite de 2MB).");
    }

    $conteudo_imagem = file_get_contents($foto['tmp_name']);

    try {
        $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id_personalizado = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $conteudo_imagem, PDO::PARAM_LOB);
        $stmt->bindParam(2, $id_usuario, PDO::PARAM_STR); // importante: string!
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
