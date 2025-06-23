<?php
session_start();
header('Content-Type: application/json');
require_once('../backend/config.php'); 

$cpf = $_POST['cpf'] ?? '';
$embalagem = $_POST['embalagem'] ?? '';

$pontos_por_tipo = [
    'shampoo' => 150,
    'condicionador' => 150,
    'maquiagem' => 100,
    'perfume' => 250
];

if (!$cpf || !$embalagem) {
    echo json_encode(['erro' => 'CPF ou embalagem não informados']);
    exit;
}

if (!isset($pontos_por_tipo[$embalagem])) {
    echo json_encode(['erro' => 'Embalagem não reconhecida', 'bloquear' => true]);
    exit;
}

$pontos = $pontos_por_tipo[$embalagem];

$conn = get_connection();
$stmt = $conn->prepare("INSERT INTO descartes (cpf, embalagem, pontos) VALUES (?, ?, ?)");
$stmt->execute([$cpf, $embalagem, $pontos]);

echo json_encode(['mensagem' => 'Descarte aceito', 'pontos' => $pontos, 'bloquear' => false]);
?>