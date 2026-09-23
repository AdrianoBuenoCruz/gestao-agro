<?php

/**
 * Registros de colheita: quanto foi colhido, de qual cultura,
 * a produtividade alcançada e pra onde foi (venda, armazém etc).
 */
class Producao
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
            "CREATE TABLE IF NOT EXISTS producao_colheitas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cultura VARCHAR(100),
                area_colhida DECIMAL(10,2),
                quantidade_colhida DECIMAL(10,2),
                produtividade DECIMAL(10,2),
                data_colheita DATE NULL,
                destino VARCHAR(100),
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function salvar(array $dados): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO producao_colheitas (cultura, area_colhida, quantidade_colhida, produtividade, data_colheita, destino)
             VALUES (:cultura, :area_colhida, :quantidade_colhida, :produtividade, :data_colheita, :destino)"
        );

        $stmt->execute([
            ':cultura'            => $dados['cultura'] ?: null,
            ':area_colhida'       => $dados['area_colhida'] !== '' ? (float) $dados['area_colhida'] : null,
            ':quantidade_colhida' => $dados['quantidade_colhida'] !== '' ? (float) $dados['quantidade_colhida'] : null,
            ':produtividade'      => $dados['produtividade'] !== '' ? (float) $dados['produtividade'] : null,
            ':data_colheita'      => $dados['data_colheita'] ?: null,
            ':destino'            => $dados['destino'] ?: null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listar(): array
    {
        return $this->pdo->query("SELECT * FROM producao_colheitas ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM producao_colheitas WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
