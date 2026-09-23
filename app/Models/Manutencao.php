<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Input;
use PDO;

final class Manutencao
{
    private const TIPOS = ['Preventiva', 'Corretiva', 'Preditiva'];
    private const STATUS = ['Aberta', 'Em andamento', 'Concluída', 'Cancelada'];

    public function __construct(private PDO $pdo) {}

    public function listar(): array
    {
        $sql = "SELECT m.*, CONCAT_WS(' - ', e.identificacao, e.modelo) AS maquina
                FROM manutencoes m INNER JOIN equipamentos e ON e.id=m.equipamento_id
                ORDER BY FIELD(m.status, 'Aberta','Em andamento','Concluída','Cancelada'),
                         m.data_abertura DESC, m.id DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados): int
    {
        $id = Input::int($dados, 'id');
        $equipamento = Input::int($dados, 'equipamento_id');
        $tipo = Input::string($dados, 'tipo', 20);
        $descricao = Input::string($dados, 'descricao', 500);
        $abertura = Input::date($dados, 'data_abertura');
        $conclusao = Input::date($dados, 'data_conclusao');
        $status = Input::string($dados, 'status', 30);
        if (!$equipamento || !$abertura || $descricao === '') {
            throw new \InvalidArgumentException('Informe máquina, data de abertura e descrição.');
        }
        if (!in_array($tipo, self::TIPOS, true) || !in_array($status, self::STATUS, true)) {
            throw new \InvalidArgumentException('Tipo ou status de manutenção inválido.');
        }

        $params = [
            ':equipamento' => $equipamento, ':tipo' => $tipo, ':descricao' => $descricao,
            ':abertura' => $abertura, ':conclusao' => $conclusao, ':status' => $status,
            ':custo' => Input::nullableFloat($dados, 'custo'),
            ':responsavel' => Input::nullableString($dados, 'responsavel', 120),
            ':usuario' => (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
        ];

        $this->pdo->beginTransaction();
        try {
            if ($id > 0) {
                $params[':id'] = $id;
                $stmt = $this->pdo->prepare(
                    'UPDATE manutencoes SET equipamento_id=:equipamento, tipo=:tipo,
                     descricao=:descricao, data_abertura=:abertura, data_conclusao=:conclusao,
                     status=:status, custo=:custo, responsavel=:responsavel, usuario_id=:usuario
                     WHERE id=:id'
                );
                $stmt->execute($params);
            } else {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO manutencoes
                     (equipamento_id,tipo,descricao,data_abertura,data_conclusao,status,custo,responsavel,usuario_id)
                     VALUES (:equipamento,:tipo,:descricao,:abertura,:conclusao,:status,:custo,:responsavel,:usuario)'
                );
                $stmt->execute($params);
                $id = (int) $this->pdo->lastInsertId();
            }

            $consultaAbertas = $this->pdo->prepare(
                "SELECT COUNT(*) FROM manutencoes WHERE equipamento_id=? AND status IN ('Aberta','Em andamento')"
            );
            $consultaAbertas->execute([$equipamento]);
            $statusEquipamento = (int) $consultaAbertas->fetchColumn() > 0 ? 'Em manutenção' : 'Ativo';
            $this->pdo->prepare('UPDATE equipamentos SET status=? WHERE id=?')
                ->execute([$statusEquipamento, $equipamento]);
            $this->pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function excluir(int $id): bool
    {
        $consulta = $this->pdo->prepare('SELECT equipamento_id FROM manutencoes WHERE id=?');
        $consulta->execute([$id]);
        $equipamento = (int) $consulta->fetchColumn();

        $this->pdo->beginTransaction();
        try {
            $ok = $this->pdo->prepare('DELETE FROM manutencoes WHERE id = ?')->execute([$id]);
            if ($equipamento > 0) {
                $abertas = $this->pdo->prepare(
                    "SELECT COUNT(*) FROM manutencoes WHERE equipamento_id=? AND status IN ('Aberta','Em andamento')"
                );
                $abertas->execute([$equipamento]);
                $status = (int) $abertas->fetchColumn() > 0 ? 'Em manutenção' : 'Ativo';
                $this->pdo->prepare('UPDATE equipamentos SET status=? WHERE id=?')->execute([$status, $equipamento]);
            }
            $this->pdo->commit();
            return $ok;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
