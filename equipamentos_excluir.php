<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('equipamentos.gerenciar');
require_once 'classes/Equipamento.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status' => false, 'mensagem' => 'Id inválido.']);
    exit;
}

$equipamento = new Equipamento($pdo);
$equipamento->excluir($id);

echo json_encode(['status' => true, 'mensagem' => 'Equipamento excluído.']);
