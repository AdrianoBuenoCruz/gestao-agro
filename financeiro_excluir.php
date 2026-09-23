<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('financeiro.gerenciar');
require_once 'classes/Financeiro.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo 'ID inválido.';
    exit;
}

try {
    $financeiro = new Financeiro($pdo);
    $financeiro->excluir((int) $id);
    echo 'OK';
} catch (PDOException $e) {
    echo 'Erro ao excluir: ' . $e->getMessage();
}
