<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Input;
use PDO;

final class Usuario
{
    private const NIVEIS = ['Administrador', 'Auxiliar Administrativo', 'Operador'];

    public function __construct(private PDO $pdo) {}

    public function listar(): array
    {
        return $this->pdo->query(
            'SELECT id, nome, usuario, email, nivel, ativo FROM usuarios ORDER BY ativo DESC, nome, usuario'
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados): int
    {
        $id = Input::int($dados, 'id');
        $nome = Input::string($dados, 'nome', 120);
        $usuario = Input::string($dados, 'usuario', 80);
        $email = Input::nullableString($dados, 'email', 150);
        $nivel = Input::string($dados, 'nivel', 40);
        $senha = (string) ($dados['senha'] ?? '');

        if ($nome === '' || $usuario === '') {
            throw new \InvalidArgumentException('Informe nome e usuário.');
        }
        if ($email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Informe um e-mail válido.');
        }
        if (!in_array($nivel, self::NIVEIS, true)) {
            throw new \InvalidArgumentException('Perfil de acesso inválido.');
        }
        if ($id === 0 && strlen($senha) < 8) {
            throw new \InvalidArgumentException('A senha inicial deve possuir pelo menos 8 caracteres.');
        }

        $params = [
            ':nome' => $nome, ':usuario' => $usuario, ':email' => $email,
            ':nivel' => $nivel, ':ativo' => !empty($dados['ativo']) ? 1 : 0,
        ];

        if ($id > 0) {
            $params[':id'] = $id;
            if ($senha !== '') {
                if (strlen($senha) < 8) {
                    throw new \InvalidArgumentException('A nova senha deve possuir pelo menos 8 caracteres.');
                }
                $params[':senha'] = password_hash($senha, PASSWORD_DEFAULT);
                $sql = 'UPDATE usuarios SET nome=:nome, usuario=:usuario, email=:email,
                        nivel=:nivel, ativo=:ativo, senha=:senha WHERE id=:id';
            } else {
                $sql = 'UPDATE usuarios SET nome=:nome, usuario=:usuario, email=:email,
                        nivel=:nivel, ativo=:ativo WHERE id=:id';
            }
            $this->pdo->prepare($sql)->execute($params);
            return $id;
        }

        $params[':senha'] = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, usuario, email, senha, nivel, ativo)
             VALUES (:nome, :usuario, :email, :senha, :nivel, :ativo)'
        );
        $stmt->execute($params);
        return (int) $this->pdo->lastInsertId();
    }

    public function excluir(int $id, int $usuarioAtual): bool
    {
        if ($id === $usuarioAtual) {
            throw new \InvalidArgumentException('Você não pode excluir o próprio usuário.');
        }
        $stmt = $this->pdo->prepare('DELETE FROM usuarios WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
