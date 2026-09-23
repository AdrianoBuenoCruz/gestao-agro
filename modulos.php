<?php

declare(strict_types=1);

use App\Controllers\PainelController;
use App\Core\Auth;

require_once __DIR__ . '/app/bootstrap.php';

Auth::requirePage();
extract((new PainelController($pdo))->index(), EXTR_SKIP);

function moeda(float|int|string|null $valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

require BASE_PATH . '/app/Views/painel.php';
