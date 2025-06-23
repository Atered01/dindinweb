<?php
// Em Dindinweb/PHP/resgatar_recompensa.php
require_once('config.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/templates/login.php');
    exit();
}

$id_recompensa = $_GET['id'] ?? null;
if (!$id_recompensa) {
    $_SESSION['erro_resgate'] = "Recompensa inválida.";
    header('Location: ../templates/recompensas.php');
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

try {
    // Inicia a transação para garantir a integridade dos dados
    $pdo->beginTransaction();

    // 1. Busca os dados da recompensa e o saldo do usuário com um bloqueio de linha (FOR UPDATE)
    $stmt = $pdo->prepare("
        SELECT r.custo_em_ddv, r.validade_dias, e.saldo_ddv 
        FROM recompensas r, estatisticas_usuario e
        WHERE r.id = ? AND e.usuario_id_personalizado = ? FOR UPDATE
    ");
    $stmt->execute([$id_recompensa, $id_usuario]);
    $dados = $stmt->fetch();

    if (!$dados) {
        throw new Exception("Recompensa ou usuário não encontrado.");
    }

    // 2. Verifica se o usuário tem saldo suficiente
    if ($dados['saldo_ddv'] < $dados['custo_em_ddv']) {
        throw new Exception("Você não tem saldo suficiente para resgatar este voucher.");
    }

    // 3. Deduz o valor do saldo do usuário
    $novo_saldo = $dados['saldo_ddv'] - $dados['custo_em_ddv'];
    $stmt_update = $pdo->prepare("UPDATE estatisticas_usuario SET saldo_ddv = ? WHERE usuario_id_personalizado = ?");
    $stmt_update->execute([$novo_saldo, $id_usuario]);

    // 4. Gera o voucher e insere na tabela de resgatados
    $codigo_voucher = 'DDV-' . strtoupper(uniqid());
    $data_expiracao = (new DateTime())->modify('+' . $dados['validade_dias'] . ' days')->format('Y-m-d');

    $stmt_insert = $pdo->prepare(
        "INSERT INTO recompensas_resgatadas (usuario_id_personalizado, recompensa_id, codigo_voucher, data_expiracao) VALUES (?, ?, ?, ?)"
    );
    $stmt_insert->execute([$id_usuario, $id_recompensa, $codigo_voucher, $data_expiracao]);

    // Se tudo deu certo, confirma a transação
    $pdo->commit();

    $_SESSION['sucesso_resgate'] = "Voucher resgatado com sucesso! Você pode vê-lo no seu perfil.";
} catch (Exception $e) {
    // Se algo deu errado, desfaz tudo
    $pdo->rollBack();
    $_SESSION['erro_resgate'] = "Erro ao resgatar voucher: " . $e->getMessage();
}

header('Location: ../templates/recompensas.php');
exit();
