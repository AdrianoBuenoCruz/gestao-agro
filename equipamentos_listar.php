<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('equipamentos.ver');
require_once 'classes/Equipamento.php';

header('Content-Type: application/json; charset=utf-8');

$equipamento = new Equipamento($pdo);
echo json_encode($equipamento->listarPorTipo($_GET['tipo'] ?? ''));
