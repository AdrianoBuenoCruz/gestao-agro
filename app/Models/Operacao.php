<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Input;
use PDO;

final class Operacao
{
    private const STATUS = ['Planejada', 'Em andamento', 'Concluída', 'Cancelada'];

    public function __construct(private PDO $pdo) {}

    public function listar(): array
    {
        $sql = "SELECT o.*, CONCAT_WS(' - ', e.identificacao, e.modelo) AS maquina,
                       c.nome AS operador
                FROM operacoes o
                INNER JOIN equipamentos e ON e.id = o.equipamento_id
                INNER JOIN colaboradores c ON c.id = o.operador_id
                ORDER BY o.data_operacao DESC, o.hora_inicio DESC, o.id DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados): int
    {
        $id = Input::int($dados, 'id');
        $equipamento = Input::int($dados, 'equipamento_id');
        $operador = Input::int($dados, 'operador_id');
        $data = Input::date($dados, 'data_operacao');
        $inicio = Input::time($dados, 'hora_inicio');
        $termino = Input::time($dados, 'hora_termino');
        $status = Input::string($dados, 'status', 30);
        if (!$equipamento || !$operador || !$data || !$inicio) {
            throw new \InvalidArgumentException('Informe máquina, operador, data e hora de início.');
        }
        if (!in_array($status, self::STATUS, true)) {
            throw new \InvalidArgumentException('Status da operação inválido.');
        }
        if ($termino && $termino < $inicio) {
            throw new \InvalidArgumentException('A hora de término não pode ser anterior ao início.');
        }

        $params = [
            ':equipamento' => $equipamento,
            ':operador' => $operador,
            ':data' => $data,
            ':consumo' => Input::nullableFloat($dados, 'consumo_medio'),
            ':inicio' => $inicio,
            ':termino' => $termino,
            ':status' => $status,
            ':descricao' => Input::nullableString($dados, 'descricao', 500),
            ':usuario' => (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
        ];

        if ($id > 0) {
            $params[':id'] = $id;
            $stmt = $this->pdo->prepare(
                'UPDATE operacoes SET equipamento_id=:equipamento, operador_id=:operador,
                 data_operacao=:data, consumo_medio=:consumo, hora_inicio=:inicio,
                 hora_termino=:termino, status=:status, descricao=:descricao,
                 usuario_id=:usuario WHERE id=:id'
            );
            $stmt->execute($params);
            $this->sincronizarMaquina($equipamento, $status);
            return $id;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO operacoes
             (equipamento_id, operador_id, data_operacao, consumo_medio, hora_inicio,
              hora_termino, status, descricao, usuario_id)
             VALUES (:equipamento, :operador, :data, :consumo, :inicio,
                     :termino, :status, :descricao, :usuario)'
        );
        $stmt->execute($params);
        $this->sincronizarMaquina($equipamento, $status);
        return (int) $this->pdo->lastInsertId();
    }

    public function excluir(int $id): bool
    {
        return $this->pdo->prepare('DELETE FROM operacoes WHERE id = ?')->execute([$id]);
    }

    private function sincronizarMaquina(int $equipamento, string $statusOperacao): void
    {
        if ($statusOperacao === 'Planejada') {
            return;
        }
        if ($statusOperacao === 'Em andamento') {
            $this->pdo->prepare("UPDATE equipamentos SET status='Operando' WHERE id=? AND status<>'Em manutenção'")
                ->execute([$equipamento]);
            return;
        }

        $manutencao = $this->pdo->prepare(
            "SELECT COUNT(*) FROM manutencoes WHERE equipamento_id=? AND status IN ('Aberta','Em andamento')"
        );
        $manutencao->execute([$equipamento]);
        $status = (int) $manutencao->fetchColumn() > 0 ? 'Em manutenção' : 'Ativo';
        $this->pdo->prepare('UPDATE equipamentos SET status=? WHERE id=?')->execute([$status, $equipamento]);
    }
}
