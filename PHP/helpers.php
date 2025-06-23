<?php
// Em Dindinweb/PHP/helpers.php

// Garante que este arquivo não seja acessado diretamente
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    die('Acesso direto não permitido.');
}

/**
 * Gera um ID Personalizado aleatório e garante que ele seja único no banco.
 * @param PDO $pdo A instância da conexão PDO.
 * @return string O novo ID Personalizado único gerado.
 */
function gerarIdPersonalizado($pdo)
{
    do {
        // Gera um número aleatório de 6 dígitos e concatena com o ano atual
        $id = rand(100000, 999999) . date('Y');

        // Prepara uma consulta para verificar se o ID já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_personalizado = ?");
        $stmt->execute([$id]);
        $existe = $stmt->fetchColumn();

        // Se $existe for maior que 0, o loop continua até encontrar um ID único
    } while ($existe);

    return $id;
}


/**
 * Determina o nome do nível do usuário com base na sua pontuação de impacto.
 */
function determinarNivel($pontuacao)
{
    if ($pontuacao >= 30000) return 'Reciclador Ouro';
    if ($pontuacao >= 15000) return 'Reciclador Prata';
    if ($pontuacao >= 5000) return 'Reciclador Bronze';
    return 'Reciclador Iniciante';
}

/**
 * Calcula a porcentagem de progresso para o próximo nível.
 */
function calcularProgresso($pontuacao, &$proximoNivelTexto)
{
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

function gerarSlugUnico($pdo, $tabela, $texto)
{
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $texto)));
    $slug_base = $slug;
    $contador = 1;

    // Verifica se o slug já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$tabela` WHERE slug = ?");
    $stmt->execute([$slug]);

    // Se o slug já existir, adiciona um número no final até encontrar um único
    while ($stmt->fetchColumn() > 0) {
        $slug = $slug_base . '-' . $contador++;
        $stmt->execute([$slug]);
    }

    return $slug;
}
