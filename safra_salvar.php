<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('safras.gerenciar');
require_once 'classes/Safra.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $safra = new Safra($pdo);
    $id = $safra->salvar($_POST);

    echo json_encode(['status' => true, 'mensagem' => 'Safra salva com sucesso.', 'id' => $id]);
} catch (PDOException $e) {
    echo json_encode(['status' => false, 'mensagem' => 'Não deu pra salvar a safra.', 'erro' => $e->getMessage()]);
}
