<?php

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    die('Acesso direto não permitido.');
}

function gerarIdPersonalizado($pdo) {
    $stmt_last_id = $pdo->query("SELECT id FROM usuarios ORDER BY id DESC LIMIT 1");
    $last_id = $stmt_last_id->fetchColumn() ?: 0;
    $sequencial = $last_id + 1;

    $parteSequencial = str_pad($sequencial, 5, '0', STR_PAD_LEFT);
    $anoEntrada = date('Y');
    return $parteSequencial . $anoEntrada;
}

function determinarNivel($pontuacao) {
    if ($pontuacao >= 30000) return 'Reciclador Ouro';
    if ($pontuacao >= 15000) return 'Reciclador Prata';
    if ($pontuacao >= 5000) return 'Reciclador Bronze';
    return 'Reciclador Iniciante';
}
function calcularProgresso($pontuacao, &$proximoNivelTexto) {
    if ($pontuacao >= 30000) {
        $proximoNivelTexto = 'Nível Máximo!';
        return 100;
    }
    if ($pontuacao >= 15000) {
        $proximoNivelTexto = 'Progresso para Ouro';
        return (($pontuacao - 15000) / (30000 - 15000)) * 100;
    }
    if ($pontuacao >= 5000) {
        $proximoNivelTexto = 'Progresso para Prata';
        return (($pontuacao - 5000) / (15000 - 5000)) * 100;
    }
    $proximoNivelTexto = 'Progresso para Bronze';
    return ($pontuacao / 5000) * 100;
}