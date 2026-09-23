<?php

/**
 * Lançamentos financeiros (receitas e despesas) e os totais que
 * alimentam o dashboard.
 */
class Financeiro
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->garantirTabela();
    }

    private function garantirTabela(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS financeiro (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo ENUM('Receita','Despesa') NOT NULL,
                categoria VARCHAR(100) NULL,
                descricao VARCHAR(255) NOT NULL,
                valor DECIMAL(10,2) NOT NULL DEFAULT 0,
                quantidade DECIMAL(10,2) NULL,
                data_lancamento DATE NULL,
                talhao_id INT NULL,
                forma_pagamento VARCHAR(50) NULL,
                observacoes VARCHAR(255) NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    /** Insere um lançamento novo ou atualiza um existente, dependendo se veio id. */
    public function salvar(array $dados): void
    {
        $id = $dados['id'] ?? '';

        $campos = [
            $dados['tipo'] ?? '',
            $dados['categoria'] ?? null,
            $dados['descricao'] ?? '',
            $dados['valorTotal'] ?? 0,
            !empty($dados['dataFin']) ? $dados['dataFin'] : null,
            $dados['talhao_id'] ?? null,
            $dados['forma_pagamento'] ?? null,
            $dados['observacoes'] ?? null,
            !empty($dados['quantidadeFin']) ? $dados['quantidadeFin'] : null,
        ];

        if (empty($id)) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO financeiro (tipo, categoria, descricao, valor, data_lancamento, talhao_id, forma_pagamento, observacoes, quantidade)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute($campos);
            return;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE financeiro SET tipo=?, categoria=?, descricao=?, valor=?, data_lancamento=?,
                talhao_id=?, forma_pagamento=?, observacoes=?, quantidade=? WHERE id=?"
        );
        $stmt->execute([...$campos, $id]);
    }

    public function listar(): array
    {
        $sql = "SELECT id, tipo, categoria, descricao, valor, data_lancamento, talhao_id, forma_pagamento, observacoes
                FROM financeiro ORDER BY id DESC";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM financeiro WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar(int $id, array $dados): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE financeiro SET tipo=?, categoria=?, descricao=?, valor=?, data_lancamento=?,
                talhao_id=?, forma_pagamento=?, observacoes=? WHERE id=?"
        );

        $stmt->execute([
            $dados['tipo'] ?? '',
            $dados['categoria'] ?? null,
            $dados['descricao'] ?? '',
            $dados['valor'] ?? 0,
            $dados['data_lancamento'] ?? null,
            $dados['talhao_id'] ?? null,
            $dados['forma_pagamento'] ?? null,
            $dados['observacoes'] ?? null,
            $id,
        ]);
    }

    public function excluir(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM financeiro WHERE id = ?");
        $stmt->execute([$id]);
    }

    /** Soma tudo que é receita menos tudo que é despesa. */
    public function saldo(): float
    {
        return $this->totalPorTipo('Receita') - $this->totalPorTipo('Despesa');
    }

    public function totalPorTipo(string $tipo): float
    {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(valor), 0) FROM financeiro WHERE tipo = ?");
        $stmt->execute([$tipo]);

        return (float) $stmt->fetchColumn();
    }

    /** Receitas x despesas agrupadas por mês, últimos 6 meses — dados prontos pro Chart.js. */
    public function resumoMensal(): array
    {
        $sql = "SELECT DATE_FORMAT(data_lancamento, '%Y-%m') AS mes,
                       SUM(CASE WHEN tipo = 'Receita' THEN valor ELSE 0 END) AS receitas,
                       SUM(CASE WHEN tipo = 'Despesa' THEN valor ELSE 0 END) AS despesas
                FROM financeiro
                WHERE data_lancamento >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY mes ORDER BY mes";

        $linhas = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $labels = $receitas = $despesas = [];
        foreach ($linhas as $linha) {
            $labels[]   = date('M/Y', strtotime($linha['mes'] . '-01'));
            $receitas[] = (float) $linha['receitas'];
            $despesas[] = (float) $linha['despesas'];
        }

        return compact('labels', 'receitas', 'despesas');
    }
}
