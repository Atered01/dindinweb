<?php
// Garante que o config.php seja carregado para usar a BASE_URL e o $pdo
require_once('../PHP/config.php'); 
// Inclui nosso arquivo de funções reutilizáveis
require_once('../PHP/helpers.php'); 

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lista de campos obrigatórios
    $campos_obrigatorios = ['nome', 'email', 'senha', 'confirmar_senha', 'CPF', 'telefone', 'cep', 'logradouro', 'numero', 'bairro', 'cidade', 'estado'];
    
    foreach ($campos_obrigatorios as $campo) {
        if (empty($_POST[$campo])) {
            $erros['geral'] = "Todos os campos são obrigatórios.";
            break;
        }
    }

    // Validação de senha
    if (empty($erros) && $_POST['senha'] !== $_POST['confirmar_senha']) {
        $erros['geral'] = "As senhas não coincidem. Tente novamente.";
    }

    // Se não houver erros, prossiga com a inserção
    if (empty($erros)) {
        try {
            // Inicia uma transação para garantir a integridade dos dados
            $pdo->beginTransaction();

            $dados_usuario = [
                'nome' => $_POST['nome'],
                'email' => $_POST['email'],
                'senha_hash' => password_hash($_POST['senha'], PASSWORD_DEFAULT),
                'CPF' => $_POST['CPF'],
                'logradouro' => $_POST['logradouro'],
                'numero' => $_POST['numero'],
                'bairro' => $_POST['bairro'],
                'cidade' => $_POST['cidade'],
                'estado' => $_POST['estado'],
                'cep' => $_POST['cep'],
                'telefone' => $_POST['telefone']
            ];
            
            // Insere os dados básicos do usuário (sem o id_personalizado ainda)
            $sql_usuario = "INSERT INTO usuarios (nome, email, senha_hash, CPF, logradouro, numero, bairro, cidade, estado, cep, telefone) 
                            VALUES (:nome, :email, :senha_hash, :CPF, :logradouro, :numero, :bairro, :cidade, :estado, :cep, :telefone)";
            $stmt_usuario = $pdo->prepare($sql_usuario);
            $stmt_usuario->execute($dados_usuario);
            
            // Pega o ID numérico que acabou de ser criado
            $id_novo_usuario_numerico = $pdo->lastInsertId();
            
            // Gera o ID Personalizado usando a função do arquivo helpers.php
            $id_personalizado = gerarIdPersonalizado($pdo);
            
            // Atualiza o usuário recém-criado com seu ID Personalizado
            $sql_id_pers = "UPDATE usuarios SET id_personalizado = ? WHERE id = ?";
            $stmt_id_pers = $pdo->prepare($sql_id_pers);
            $stmt_id_pers->execute([$id_personalizado, $id_novo_usuario_numerico]);
            
            // Insere a linha de estatísticas usando o ID numérico
            $sql_stats = "INSERT INTO estatisticas_usuario (id_usuario) VALUES (?)";
            $stmt_stats = $pdo->prepare($sql_stats);
            $stmt_stats->execute([$id_novo_usuario_numerico]);
            
            // Se tudo deu certo, confirma as operações no banco
            $pdo->commit();

            $_SESSION['mensagem_sucesso'] = "Cadastro realizado com sucesso! Faça o login para continuar.";
            header("Location: login.php");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->errorInfo[1] == 1062) {
                $erros['geral'] = "Usuário já cadastrado com esse email!";
            } else {
                $erros['geral'] = "Erro ao cadastrar. Tente novamente.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crie sua conta - DinDin Verde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/cadastro.css">
    <script src="<?php echo BASE_URL; ?>/js/cadastro.js" defer></script>
</head>
<body>

    <?php 
        if (file_exists('../includes/header_publico.php')) {
            include '../includes/header_publico.php';
        }
    ?>

    <main>
        <div class="container" id="cadastro-container">
            <div class="header">
                <div class="icon-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
                <h1>Crie sua conta</h1>
            </div>
            <p class="subtitle">Preencha os campos abaixo para se cadastrar.</p>

            <?php if (!empty($erros['geral'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($erros['geral']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="cadastro.php">
                <div class="form-row">
                    <div class="form-group"><label for="nome">Nome completo</label><input type="text" id="nome" name="nome" required></div>
                    <div class="form-group"><label for="CPF">CPF</label><input type="text" id="CPF" name="CPF" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="email">E-mail</label><input type="email" id="email" name="email" required></div>
                    <div class="form-group"><label for="telefone">Telefone</label><input type="text" id="telefone" name="telefone" required></div>
                </div>
                <hr>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;"><label for="cep">CEP</label><input type="text" id="cep" name="cep" required maxlength="9"></div>
                    <div class="form-group" style="flex: 2;"><label for="logradouro">Rua</label><input type="text" id="logradouro" name="logradouro" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="numero">Número</label><input type="text" id="numero" name="numero" required></div>
                    <div class="form-group"><label for="bairro">Bairro</label><input type="text" id="bairro" name="bairro" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex: 2;"><label for="cidade">Cidade</label><input type="text" id="cidade" name="cidade" required></div>
                    <div class="form-group" style="flex: 1;"><label for="estado">Estado</label><input type="text" id="estado" name="estado" required></div>
                </div>
                <hr>
                <div class="form-row">
                    <div class="form-group"><label for="senha">Senha</label><input type="password" id="senha" name="senha" required minlength="6"></div>
                    <div class="form-group"><label for="confirmar_senha">Confirme a senha</label><input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6"></div>
                </div>
                <div class="button-group">
                    <button type="submit" class="register-button">Cadastrar</button>
                </div>
            </form>
            <div class="login-link">
                <p>Já tem uma conta? <a href="login.php">Logue aqui</a></p>
            </div>
        </div>
    </main>

    <?php 
        if (file_exists('../includes/footer.php')) {
            include '../includes/footer.php';
        }
    ?>
</body>
</html>