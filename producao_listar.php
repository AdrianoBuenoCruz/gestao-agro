<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('producao.gerenciar');
require_once 'classes/Producao.php';

header('Content-Type: application/json; charset=utf-8');

$producao = new Producao($pdo);
echo json_encode($producao->listar());
