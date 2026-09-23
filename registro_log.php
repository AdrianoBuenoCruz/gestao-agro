<?php

function registrarLog(PDO $pdo, int $usuario_id, string $acao, string $modulo, ?int $registro_id = null, ?string $descricao = null): void
{
    $sql = "INSERT INTO log_usuarios
            (usuario_id, acao, modulo, registro_id, descricao)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $usuario_id,
        $acao,
        $modulo,
        $registro_id,
        $descricao
    ]);
}