<?php
// Em Dindinweb/PHP/migrar_slugs.php (arquivo temporário)
require_once('config.php');
require_once('helpers.php');

echo "Iniciando migração de slugs...<br>";

try {
    $stmt = $pdo->query("SELECT id, nome FROM recompensas WHERE slug IS NULL OR slug = ''");
    $recompensas_sem_slug = $stmt->fetchAll();

    if (empty($recompensas_sem_slug)) {
        echo "Nenhuma recompensa nova para migrar. Tudo certo!";
        exit();
    }

    foreach ($recompensas_sem_slug as $recompensa) {
        $novo_slug = gerarSlugUnico($pdo, 'recompensas', $recompensa['nome']);

        $update_stmt = $pdo->prepare("UPDATE recompensas SET slug = ? WHERE id = ?");
        $update_stmt->execute([$novo_slug, $recompensa['id']]);

        echo "Slug gerado para '" . htmlspecialchars($recompensa['nome']) . "': " . $novo_slug . "<br>";
    }

    echo "<br>Migração concluída com sucesso!";

} catch (PDOException $e) {
    die("Erro durante a migração: " . $e->getMessage());
}
?>