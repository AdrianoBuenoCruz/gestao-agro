<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('financeiro.gerenciar');
require_once 'classes/Financeiro.php';

header('Content-Type: application/json; charset=utf-8');

$id = $_POST['id'] ?? null;
if (!$id) {
    echo json_encode(['status' => false, 'mensagem' => 'Id inválido']);
    exit;
}

try {
    $financeiro = new Financeiro($pdo);
    $financeiro->atualizar((int) $id, $_POST);

    echo json_encode(['status' => true, 'mensagem' => 'Registro atualizado com sucesso.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'mensagem' => 'Erro ao atualizar.', 'erro' => $e->getMessage()]);
}
