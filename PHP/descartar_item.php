<?php
// Em Dindinweb/PHP/descartar_item.php
require_once('config.php');

// Define o cabeçalho como JSON para a resposta
header('Content-Type: application/json');

// Função para enviar resposta e sair
function responder($sucesso, $mensagem)
{
    echo json_encode(['success' => $sucesso, 'message' => $mensagem]);
    exit();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    responder(false, 'Erro: Usuário não está logado.');
}

// Verifica se o método é POST e se o item foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['item_detectado'])) {
    responder(false, 'Erro: Requisição inválida.');
}

$item_detectado = $_POST['item_detectado'];
$id_usuario = $_SESSION['usuario_id'];

// Lógica de Negócios: Define os pontos e estatísticas para cada item
$recompensas_por_item = [
    'Garrafa PET' => ['ddv' => 5, 'co2' => 0.11, 'agua' => 9, 'energia' => 0.18],
    'Lata Aluminio' => ['ddv' => 8, 'co2' => 0.2, 'agua' => 8, 'energia' => 0.5],
    'Caixa Papelao' => ['ddv' => 3, 'co2' => 0.05, 'agua' => 10, 'energia' => 0.1],
    'Vidro' => ['ddv' => 4, 'co2' => 0.08, 'agua' => 2, 'energia' => 0.15]
    // Adicione mais classes do seu modelo aqui
];

// Verifica se o item detectado existe na nossa lista de recompensas
if (!array_key_exists($item_detectado, $recompensas_por_item)) {
    responder(false, 'Erro: Tipo de embalagem não reconhecido pelo sistema.');
}

$recompensa = $recompensas_por_item[$item_detectado];

// Usa uma transação para garantir que todas as atualizações ocorram ou nenhuma
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
    responder(true, 'Sucesso! +' . $recompensa['ddv'] . ' DDV adicionados à sua conta.');
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Erro ao descartar item: ' . $e->getMessage());
    responder(false, 'Erro no servidor ao processar o descarte. Tente novamente.');
}
