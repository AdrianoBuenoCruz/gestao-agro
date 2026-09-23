<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('financeiro.gerenciar');
require_once 'classes/Financeiro.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $financeiro = new Financeiro($pdo);
    echo json_encode($financeiro->listar());
} catch (PDOException $e) {
    echo json_encode(['status' => false, 'mensagem' => 'Erro ao listar registros.', 'erro' => $e->getMessage()]);
}
