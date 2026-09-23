<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('equipamentos.gerenciar');
require_once 'classes/Equipamento.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

if ($id <= 0) {
    echo json_encode(['status' => false, 'mensagem' => 'Id inválido.']);
    exit;
}

$equipamento = new Equipamento($pdo);
$ok = $equipamento->atualizarStatus($id, $status);

echo json_encode($ok
    ? ['status' => true, 'mensagem' => 'Status atualizado.']
    : ['status' => false, 'mensagem' => 'Status inválido.']);
