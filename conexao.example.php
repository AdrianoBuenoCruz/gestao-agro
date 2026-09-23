<?php
// Copie este arquivo para conexao.php e preencha apenas na sua máquina ou hospedagem.
$host = 'localhost';
$user = 'SEU_USUARIO';
$password = 'SUA_SENHA';
$db = 'SEU_BANCO';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Falha na conexão com o banco de dados.');
}
