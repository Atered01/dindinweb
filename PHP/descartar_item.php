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

$item_recebido = $_POST['item_detectado'];
$id_usuario = $_SESSION['usuario_id'];

// Lógica de Negócios: Define os pontos e estatísticas para cada item
$recompensas_por_item = [
    'Vidro de Perfume' => ['ddv' => 10, 'co2' => 0.08, 'agua' => 2, 'energia' => 0.15],
    'Garrafa PET'   => ['ddv' => 5, 'co2' => 0.11, 'agua' => 9, 'energia' => 0.18],
    'Lata Aluminio' => ['ddv' => 8, 'co2' => 0.2, 'agua' => 8, 'energia' => 0.5],
    'Caixa Papelao' => ['ddv' => 3, 'co2' => 0.05, 'agua' => 10, 'energia' => 0.1],
    'Vidro'         => ['ddv' => 4, 'co2' => 0.08, 'agua' => 2, 'energia' => 0.15]
];

// ====================================================================
// LÓGICA DE DETECÇÃO MELHORADA
// ====================================================================
$item_mapeado = null;
// Procura por uma correspondência no nome do item recebido
foreach (array_keys($recompensas_por_item) as $chave) {
    if (stripos($item_recebido, $chave) !== false) {
        $item_mapeado = $chave;
        break; // Para quando encontrar a primeira correspondência
    }
}

// Verifica se o item foi mapeado para uma de nossas chaves conhecidas
if ($item_mapeado === null) {
    responder(false, 'Erro: Tipo de embalagem não reconhecido pelo sistema.');
}

$recompensa = $recompensas_por_item[$item_mapeado];
// ====================================================================

try {
    $pdo->beginTransaction();

    $sql = "UPDATE estatisticas_usuario SET
                saldo_ddv = saldo_ddv + :ddv,
                saldo_total_acumulado = saldo_total_acumulado + :ddv,
                itens_reciclados = itens_reciclados + 1,
                co2_evitado = co2_evitado + :co2,
                agua_economizada = agua_economizada + :agua,
                energia_poupada = energia_poupada + :energia
            WHERE
                usuario_id_personalizado = :id_usuario";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':ddv' => $recompensa['ddv'],
        ':co2' => $recompensa['co2'],
        ':agua' => $recompensa['agua'],
        ':energia' => $recompensa['energia'],
        ':id_usuario' => $id_usuario
    ]);

    $pdo->commit();
    responder(true, 'Sucesso! +' . $recompensa['ddv'] . ' DDV por ' . $item_mapeado . '.');

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Erro ao descartar item: ' . $e->getMessage());
    responder(false, 'Erro no servidor ao processar o descarte. Tente novamente.');
}
?>