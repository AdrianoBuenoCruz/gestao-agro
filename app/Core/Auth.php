<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private const PERMISSIONS = [
        'Administrador' => ['*'],
        'Auxiliar Administrativo' => [
            'dashboard.ver', 'safras.gerenciar', 'producao.gerenciar',
            'financeiro.gerenciar', 'equipamentos.gerenciar',
            'colaboradores.gerenciar', 'operacoes.gerenciar',
            'manutencoes.gerenciar',
        ],
        'Operador' => [
            'dashboard.ver', 'equipamentos.ver', 'operacoes.ver',
            'operacoes.registrar', 'manutencoes.ver', 'manutencoes.registrar',
        ],
    ];

    public static function check(): bool
    {
        return isset($_SESSION['usuario_id']);
    }

    public static function role(): string
    {
        $role = trim((string) ($_SESSION['nivel'] ?? 'Operador'));
        $normalizado = mb_strtolower($role, 'UTF-8');

        return match ($normalizado) {
            'administrador', 'admin' => 'Administrador',
            'aux administrativo', 'auxiliar administrativo', 'auxiliar' => 'Auxiliar Administrativo',
            default => 'Operador',
        };
    }

    public static function user(): array
    {
        return [
            'id' => (int) ($_SESSION['usuario_id'] ?? 0),
            'nome' => (string) ($_SESSION['nome'] ?? $_SESSION['usuario'] ?? ''),
            'usuario' => (string) ($_SESSION['usuario'] ?? ''),
            'nivel' => self::role(),
        ];
    }

    public static function can(string $permission): bool
    {
        $permissions = self::PERMISSIONS[self::role()] ?? [];
        if (in_array('*', $permissions, true) || in_array($permission, $permissions, true)) {
            return true;
        }

        if (str_ends_with($permission, '.ver')) {
            $prefix = strstr($permission, '.', true);
            return in_array($prefix . '.gerenciar', $permissions, true);
        }

        if (str_ends_with($permission, '.registrar')) {
            $prefix = strstr($permission, '.', true);
            return in_array($prefix . '.gerenciar', $permissions, true);
        }

        return false;
    }

    public static function requirePage(): void
    {
        if (!self::check()) {
            header('Location: index.php');
            exit;
        }
    }

    public static function requireJson(string $permission): void
    {
        if (!self::check()) {
            JsonResponse::send(['status' => false, 'mensagem' => 'Sessão expirada. Entre novamente.'], 401);
        }
        if (!self::can($permission)) {
            JsonResponse::send(['status' => false, 'mensagem' => 'Seu perfil não possui permissão para esta ação.'], 403);
        }
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? '';
        if (!is_string($token) || !hash_equals(self::csrfToken(), $token)) {
            JsonResponse::send(['status' => false, 'mensagem' => 'A sessão de segurança expirou. Atualize a página.'], 419);
        }
    }
}

