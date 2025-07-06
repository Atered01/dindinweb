<?php
// Em Dindinweb/PHP/descartar_item.php
require_once('config.php');

header('Content-Type: application/json');

function responder($sucesso, $mensagem) {
    echo json_encode(['success' => $sucesso, 'message' => $mensagem]);
    exit();
}

if (!isset($_SESSION['usuario_id'])) {
    responder(false, 'Erro: Usuário não está logado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['item_detectado'])) {
    responder(false, 'Erro: Requisição inválida.');
}

$id_ia_recebido = $_POST['item_detectado'];
$id_usuario = $_SESSION['usuario_id'];

try {
    $pdo->beginTransaction();

    // 1. Encontra o produto no banco de dados usando o ID da IA
    $stmt_produto = $pdo->prepare("SELECT * FROM produtos WHERE id_ia = ?");
    $stmt_produto->execute([$id_ia_recebido]);
    $produto = $stmt_produto->fetch();

    if (!$produto) {
        responder(false, 'Erro: Embalagem não cadastrada ou não reconhecida.');
    }

    // 2. Insere o registro na nova tabela 'descartes'
    $stmt_descarte = $pdo->prepare("INSERT INTO descartes (usuario_id_personalizado, produto_id) VALUES (?, ?)");
    $stmt_descarte->execute([$id_usuario, $produto['id']]);

    // 3. Atualiza as estatísticas do usuário
    $sql_update = "UPDATE estatisticas_usuario SET
                saldo_ddv = saldo_ddv + :ddv,
                saldo_total_acumulado = saldo_total_acumulado + :ddv,
                itens_reciclados = itens_reciclados + 1,
                co2_evitado = co2_evitado + :co2,
                agua_economizada = agua_economizada + :agua,
                energia_poupada = energia_poupada + :energia
            WHERE
                usuario_id_personalizado = :id_usuario";
    
    $stmt_update_stats = $pdo->prepare($sql_update);
    $stmt_update_stats->execute([
        ':ddv' => $produto['pontos_ddv'],
        ':co2' => $produto['co2_evitado'],
        ':agua' => $produto['agua_economizada'],
        ':energia' => $produto['energia_poupada'],
        ':id_usuario' => $id_usuario
    ]);

    $pdo->commit();
    responder(true, 'Sucesso! +' . $produto['pontos_ddv'] . ' DDV por ' . $produto['nome_produto'] . '.');

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Erro ao descartar item: ' . $e->getMessage());
    responder(false, 'Erro no servidor ao processar o descarte. Tente novamente.');
}
?>