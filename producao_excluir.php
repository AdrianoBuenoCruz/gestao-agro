<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('producao.gerenciar');
require_once 'classes/Producao.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status' => false, 'mensagem' => 'Id inválido.']);
    exit;
}

$producao = new Producao($pdo);
$producao->excluir($id);

echo json_encode(['status' => true, 'mensagem' => 'Registro excluído.']);
