<?php

/**
 * Cadastro de safras: cultura, área plantada, talhão e as datas de
 * plantio / previsão de colheita.
 */
class Safra
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
            "CREATE TABLE IF NOT EXISTS safras (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100),
                cultura VARCHAR(100),
                area DECIMAL(10,2),
                talhao VARCHAR(100),
                data_plantio DATE NULL,
                previsao_colheita DATE NULL,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function salvar(array $dados): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO safras (nome, cultura, area, talhao, data_plantio, previsao_colheita)
             VALUES (:nome, :cultura, :area, :talhao, :plantio, :colheita)"
        );

        $stmt->execute([
            ':nome'     => $dados['nome'] ?: null,
            ':cultura'  => $dados['cultura'] ?: null,
            ':area'     => $dados['area'] !== '' ? (float) $dados['area'] : null,
            ':talhao'   => $dados['talhao'] ?: null,
            ':plantio'  => $dados['data_plantio'] ?: null,
            ':colheita' => $dados['previsao_colheita'] ?: null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listar(): array
    {
        return $this->pdo->query("SELECT * FROM safras ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM safras WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
