<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Dashboard;
use App\Models\Fazenda;
use PDO;

final class PainelController
{
    public function __construct(private PDO $pdo) {}

    public function index(): array
    {
        require_once BASE_PATH . '/classes/Equipamento.php';
        require_once BASE_PATH . '/classes/Financeiro.php';

        $erro = '';
        try {
            $equipamento = new \Equipamento($this->pdo);
            $financeiro = Auth::can('financeiro.gerenciar') ? new \Financeiro($this->pdo) : null;
            $mensal = $financeiro ? $financeiro->resumoMensal() : ['labels' => [], 'receitas' => [], 'despesas' => []];
            $novos = (new Dashboard($this->pdo))->indicadores();

            return array_merge([
                'erro' => '',
                'page_title' => 'Painel',
                'equipManutencao' => $equipamento->totalEmManutencao(),
                'saldo' => $financeiro ? $financeiro->saldo() : 0.0,
                'labelsMes' => $mensal['labels'],
                'dadosReceitas' => $mensal['receitas'],
                'dadosDespesas' => $mensal['despesas'],
                'fazenda' => (new Fazenda($this->pdo))->obter(),
            ], $novos);
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return [
                'erro' => 'Execute o arquivo database_atualizacao_modulos.sql antes de acessar esta versão.',
                'page_title' => 'Painel', 'equipManutencao' => 0, 'saldo' => 0.0,
                'labelsMes' => [], 'dadosReceitas' => [], 'dadosDespesas' => [],
                'areaColhida' => 0, 'quantidadeColhida' => 0, 'produtividadeMedia' => 0,
                'operacoesAtivas' => 0, 'colaboradoresAtivos' => 0, 'manutencoesAbertas' => 0,
                'culturas' => [], 'producaoPorCultura' => [],
                'fazenda' => ['nome'=>'FN Agropecuária','area_total_ha'=>0,'area_produtiva_ha'=>0,'municipio'=>'Itaberaí','estado'=>'GO','cultura_principal'=>''],
            ];
        }
    }
}
