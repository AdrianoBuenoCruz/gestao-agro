<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Input;
use PDO;

final class Colaborador
{
    private const FORMAS = ['Diária', 'CLT', 'Quinzenal', 'Hora'];

    public function __construct(private PDO $pdo) {}

    public function listar(bool $somenteAtivos = false): array
    {
        $where = $somenteAtivos ? 'WHERE c.ativo = 1' : '';
        $sql = "SELECT c.*, CONCAT_WS(' - ', e.identificacao, e.modelo) AS maquina
                FROM colaboradores c
                LEFT JOIN equipamentos e ON e.id = c.equipamento_id
                {$where}
                ORDER BY c.ativo DESC, c.nome";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados): int
    {
        $id = Input::int($dados, 'id');
        $nome = Input::string($dados, 'nome', 120);
        $forma = Input::string($dados, 'forma_pagamento', 20);
        if ($nome === '') {
            throw new \InvalidArgumentException('Informe o nome do colaborador.');
        }
        if (!in_array($forma, self::FORMAS, true)) {
            throw new \InvalidArgumentException('Selecione uma forma de pagamento válida.');
        }

        $params = [
            ':nome' => $nome,
            ':cpf' => Input::nullableString($dados, 'cpf', 14),
            ':telefone' => Input::nullableString($dados, 'telefone', 20),
            ':cargo' => Input::nullableString($dados, 'cargo', 100),
            ':forma' => $forma,
            ':valor' => Input::nullableFloat($dados, 'valor_pagamento'),
            ':equipamento' => Input::int($dados, 'equipamento_id') ?: null,
            ':admissao' => Input::date($dados, 'data_admissao'),
            ':ativo' => !empty($dados['ativo']) ? 1 : 0,
            ':observacoes' => Input::nullableString($dados, 'observacoes', 500),
        ];

        if ($id > 0) {
            $params[':id'] = $id;
            $stmt = $this->pdo->prepare(
                'UPDATE colaboradores SET nome=:nome, cpf=:cpf, telefone=:telefone, cargo=:cargo,
                 forma_pagamento=:forma, valor_pagamento=:valor, equipamento_id=:equipamento,
                 data_admissao=:admissao, ativo=:ativo, observacoes=:observacoes WHERE id=:id'
            );
            $stmt->execute($params);
            return $id;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO colaboradores
             (nome, cpf, telefone, cargo, forma_pagamento, valor_pagamento, equipamento_id, data_admissao, ativo, observacoes)
             VALUES (:nome, :cpf, :telefone, :cargo, :forma, :valor, :equipamento, :admissao, :ativo, :observacoes)'
        );
        $stmt->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM colaboradores WHERE id = ?');
        return $stmt->execute([$id]);
    }
}

