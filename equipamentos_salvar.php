<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('equipamentos.gerenciar');
require_once 'classes/Equipamento.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $equipamento = new Equipamento($pdo);
    $id = $equipamento->salvar($_POST);

    echo json_encode(['status' => true, 'mensagem' => ucfirst($_POST['tipo']) . ' salvo(a) com sucesso.', 'id' => $id]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['status' => false, 'mensagem' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['status' => false, 'mensagem' => 'Não deu pra salvar o equipamento.', 'erro' => $e->getMessage()]);
}
