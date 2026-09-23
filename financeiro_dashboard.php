<?php
require_once 'app/bootstrap.php';
\App\Core\Auth::requireJson('financeiro.gerenciar');
require_once 'classes/Financeiro.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $financeiro = new Financeiro($pdo);

    $saldo = $financeiro->saldo();
    $mensal = $financeiro->resumoMensal();

    echo json_encode([
        'status'         => true,
        'totalReceitas'  => $financeiro->totalPorTipo('Receita'),
        'totalDespesas'  => $financeiro->totalPorTipo('Despesa'),
        'saldo'          => $saldo,
        'saldoFormatado' => 'R$ ' . number_format($saldo, 2, ',', '.'),
        'labels'         => $mensal['labels'],
        'receitas'       => $mensal['receitas'],
        'despesas'       => $mensal['despesas'],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'mensagem' => 'Erro ao carregar dados do dashboard.', 'erro' => $e->getMessage()]);
}
