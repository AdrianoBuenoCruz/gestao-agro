<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('financeiro.gerenciar');
require_once 'classes/Financeiro.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $financeiro = new Financeiro($pdo);
    $financeiro->salvar($_POST);

    $mensagem = empty($_POST['id']) ? 'Registro cadastrado com sucesso.' : 'Registro atualizado com sucesso.';
    echo json_encode(['status' => true, 'mensagem' => $mensagem]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'mensagem' => 'Erro ao salvar no banco.', 'erro' => $e->getMessage()]);
}
