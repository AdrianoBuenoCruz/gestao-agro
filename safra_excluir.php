<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('safras.gerenciar');
require_once 'classes/Safra.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status' => false, 'mensagem' => 'Id inválido.']);
    exit;
}

$safra = new Safra($pdo);
$safra->excluir($id);

echo json_encode(['status' => true, 'mensagem' => 'Safra excluída.']);
