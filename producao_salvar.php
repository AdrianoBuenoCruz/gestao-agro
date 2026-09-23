<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('producao.gerenciar');
require_once 'classes/Producao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $producao = new Producao($pdo);
    $id = $producao->salvar($_POST);

    echo json_encode(['status' => true, 'mensagem' => 'Produção salva com sucesso.', 'id' => $id]);
} catch (PDOException $e) {
    echo json_encode(['status' => false, 'mensagem' => 'Não deu pra salvar a produção.', 'erro' => $e->getMessage()]);
}
