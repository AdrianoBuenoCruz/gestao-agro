<?php

/**
 * Cuida do cadastro de máquinas da fazenda: trator, colheitadeira,
 * plantadeira e pulverizador.
 */
class Equipamento
{
    private PDO $pdo;

    private const STATUS_PERMITIDOS = [
        'Ativo',
        'Operando',
        'Em manutenção',
        'Inativo'
    ];

    private const TIPOS_PERMITIDOS = [
        'Trator',
        'Colheitadeira',
        'Plantadeira',
        'Pulverizador'
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->garantirTabela();
    }

    /**
     * Cria a tabela caso ela ainda não exista.
     */
    private function garantirTabela(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS equipamentos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tipo VARCHAR(50) NOT NULL DEFAULT 'Trator',
                modelo VARCHAR(100),
                marca VARCHAR(100),
                ano INT,
                identificacao VARCHAR(50),
                horimetro DECIMAL(10,2),
                potencia VARCHAR(50),
                status ENUM(
                    'Ativo',
                    'Operando',
                    'Em manutenção',
                    'Inativo'
                ) NOT NULL DEFAULT 'Ativo',
                manutencao_preventiva_data DATE NULL,
                observacoes VARCHAR(255),
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                atualizado_em TIMESTAMP
                    DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    /**
     * Salva um novo equipamento.
     *
     * @param array $dados
     * @return int ID do equipamento criado.
     */
    public function salvar(array $dados): int
    {
        if (
            !in_array(
                $dados['tipo'] ?? '',
                self::TIPOS_PERMITIDOS,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Tipo de equipamento inválido.'
            );
        }

        $status = $dados['status'] ?? 'Ativo';

        if (
            !in_array(
                $status,
                self::STATUS_PERMITIDOS,
                true
            )
        ) {
            $status = 'Ativo';
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO equipamentos (
                tipo,
                modelo,
                marca,
                ano,
                identificacao,
                horimetro,
                potencia,
                status,
                manutencao_preventiva_data,
                observacoes
            ) VALUES (
                :tipo,
                :modelo,
                :marca,
                :ano,
                :identificacao,
                :horimetro,
                :potencia,
                :status,
                :manutencao,
                :observacoes
            )"
        );

        $stmt->execute([
            ':tipo' => $dados['tipo'],
            ':modelo' => !empty($dados['modelo'])
                ? $dados['modelo']
                : null,
            ':marca' => !empty($dados['marca'])
                ? $dados['marca']
                : null,
            ':ano' => isset($dados['ano']) && $dados['ano'] !== ''
                ? (int) $dados['ano']
                : null,
            ':identificacao' => !empty($dados['identificacao'])
                ? $dados['identificacao']
                : null,
            ':horimetro' => isset($dados['horimetro'])
                && $dados['horimetro'] !== ''
                    ? (float) $dados['horimetro']
                    : null,
            ':potencia' => !empty($dados['especificacao'])
                ? $dados['especificacao']
                : null,
            ':status' => $status,
            ':manutencao' =>
                !empty($dados['manutencao_preventiva_data'])
                    ? $dados['manutencao_preventiva_data']
                    : null,
            ':observacoes' => !empty($dados['observacoes'])
                ? $dados['observacoes']
                : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Lista os equipamentos de determinado tipo.
     */
    public function listarPorTipo(string $tipo): array
    {
        if (
            !in_array(
                $tipo,
                self::TIPOS_PERMITIDOS,
                true
            )
        ) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            "SELECT *
             FROM equipamentos
             WHERE tipo = :tipo
             ORDER BY id DESC"
        );

        $stmt->execute([
            ':tipo' => $tipo
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza somente o status do equipamento.
     */
    public function atualizarStatus(
        int $id,
        string $status
    ): bool {
        if (
            $id <= 0 ||
            !in_array(
                $status,
                self::STATUS_PERMITIDOS,
                true
            )
        ) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE equipamentos
             SET status = :status
             WHERE id = :id"
        );

        $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Exclui o equipamento quando não existem registros vinculados.
     *
     * Retorna false caso o equipamento possua operações
     * ou outros registros relacionados.
     */
    public function excluir(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM equipamentos
                 WHERE id = :id"
            );

            $stmt->execute([
                ':id' => $id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $codigoSql = $e->getCode();
            $codigoMysql = $e->errorInfo[1] ?? null;

            if (
                $codigoSql === '23000' ||
                (int) $codigoMysql === 1451
            ) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Retorna a quantidade de equipamentos em manutenção.
     */
    public function totalEmManutencao(): int
    {
        $stmt = $this->pdo->query(
            "SELECT COUNT(*)
             FROM equipamentos
             WHERE status = 'Em manutenção'"
        );

        return (int) $stmt->fetchColumn();
    }
}