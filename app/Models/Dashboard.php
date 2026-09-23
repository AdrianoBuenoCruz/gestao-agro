<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Dashboard
{
    public function __construct(private PDO $pdo) {}

    public function indicadores(): array
    {
        $producao = $this->pdo->query(
            'SELECT COALESCE(SUM(area_colhida),0) area_colhida,
                    COALESCE(SUM(quantidade_colhida),0) quantidade_colhida,
                    COALESCE(AVG(NULLIF(produtividade,0)),0) produtividade_media
             FROM producao_colheitas'
        )->fetch(PDO::FETCH_ASSOC);

        $porCultura = $this->pdo->query(
            'SELECT COALESCE(NULLIF(cultura,\'\'),\'Não informada\') cultura,
                    COALESCE(SUM(quantidade_colhida),0) quantidade
             FROM producao_colheitas GROUP BY cultura ORDER BY quantidade DESC LIMIT 8'
        )->fetchAll(PDO::FETCH_ASSOC);

        return [
            'areaColhida' => (float) ($producao['area_colhida'] ?? 0),
            'quantidadeColhida' => (float) ($producao['quantidade_colhida'] ?? 0),
            'produtividadeMedia' => (float) ($producao['produtividade_media'] ?? 0),
            'operacoesAtivas' => (int) $this->pdo->query(
                "SELECT COUNT(*) FROM operacoes WHERE status IN ('Planejada','Em andamento')"
            )->fetchColumn(),
            'colaboradoresAtivos' => (int) $this->pdo->query(
                'SELECT COUNT(*) FROM colaboradores WHERE ativo=1'
            )->fetchColumn(),
            'manutencoesAbertas' => (int) $this->pdo->query(
                "SELECT COUNT(*) FROM manutencoes WHERE status IN ('Aberta','Em andamento')"
            )->fetchColumn(),
            'culturas' => array_column($porCultura, 'cultura'),
            'producaoPorCultura' => array_map('floatval', array_column($porCultura, 'quantidade')),
        ];
    }
}

