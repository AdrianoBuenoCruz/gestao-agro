<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $credential = $_POST["credential"] ?? "";

    if (!empty($credential)) {
        $_SESSION["usuario"] = "Usuário Google";
        echo "sucesso";
        exit;
    } else {
        echo "Token não recebido";
        exit;
    }
}

echo "Requisição inválida";