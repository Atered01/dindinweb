<?php
// Em Dindinweb/PHP/solicitar_recuperacao.php
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../templates/esqueci_senha.php');
    exit();
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if ($email) {
    $stmt = $pdo->prepare("SELECT id_personalizado FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Gera um código numérico de 7 dígitos seguro
        $codigo = random_int(1000000, 9999999);
        $token_hash = hash('sha256', (string)$codigo);
        
        $data_expiracao = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s'); // Token curto, validade curta

        // Invalida tokens antigos para o mesmo e-mail antes de inserir o novo
        $stmt_delete_old = $pdo->prepare("DELETE FROM recuperacao_senha WHERE usuario_email = ?");
        $stmt_delete_old->execute([$email]);

        // Salva o hash do novo código no banco
        $sql_insert = "INSERT INTO recuperacao_senha (usuario_email, token_hash, data_expiracao) VALUES (?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->execute([$email, $token_hash, $data_expiracao]);

        // Monta e envia o e-mail
        $assunto = "Seu Código de Recuperação - DinDin Verde";
        $corpo = "Olá,\n\nSeu código para redefinição de senha é: " . $codigo . "\n\n";
        $corpo .= "Este código é válido por 15 minutos.\n\nSe você não solicitou isso, pode ignorar este e-mail.\n";
        $headers = "From: nao-responda@dindinverde.com.br";

        @mail($email, $assunto, $corpo, $headers);
    }
}

// Redireciona o usuário para a tela de verificação do código
// Passamos o e-mail na URL para preencher o campo automaticamente
header('Location: ../templates/verificar_codigo.php?email=' . urlencode($email));
exit();
?>