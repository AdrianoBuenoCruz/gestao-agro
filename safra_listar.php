<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('safras.gerenciar');
require_once 'classes/Safra.php';

header('Content-Type: application/json; charset=utf-8');

$safra = new Safra($pdo);
echo json_encode($safra->listar());
