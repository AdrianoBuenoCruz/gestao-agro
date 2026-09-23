<?php

declare(strict_types=1);

namespace App\Core;

final class JsonResponse
{
    public static function send(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error(string $message, int $status = 422): void
    {
        self::send(['status' => false, 'mensagem' => $message], $status);
    }
}
