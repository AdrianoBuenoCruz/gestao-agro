<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('financeiro.gerenciar');
require_once 'classes/Financeiro.php';

header('Content-Type: application/json; charset=utf-8');

$financeiro = new Financeiro($pdo);
echo json_encode($financeiro->buscarPorId((int) ($_GET['id'] ?? 0)));
