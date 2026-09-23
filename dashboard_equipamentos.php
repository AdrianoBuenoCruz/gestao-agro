<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('dashboard.ver');
require_once 'classes/Equipamento.php';

header('Content-Type: application/json; charset=utf-8');

$equipamento = new Equipamento($pdo);
echo json_encode(['status' => true, 'total' => $equipamento->totalEmManutencao()]);
